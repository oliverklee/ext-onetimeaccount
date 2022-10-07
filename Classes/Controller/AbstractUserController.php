<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUserGroup;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserGroupRepository;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Onetimeaccount\Service\CredentialsGenerator;
use OliverKlee\Onetimeaccount\Validation\UserValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Base class to implement most of the functionality of the plugin except for the specifics of what should
 * happen after a user has been created (autologin or storing the user UID in the session).
 */
abstract class AbstractUserController extends ActionController
{
    /**
     * @var FrontendUserRepository
     */
    protected $userRepository;

    /**
     * @var FrontendUserGroupRepository
     */
    protected $userGroupRepository;

    /**
     * @var CredentialsGenerator
     */
    protected $credentialsGenerator;

    /**
     * @var UserValidator
     */
    protected $userValidator;

    public function injectFrontendUserRepository(FrontendUserRepository $repository): void
    {
        $this->userRepository = $repository;
    }

    public function injectFrontendUserGroupRepository(FrontendUserGroupRepository $repository): void
    {
        $this->userGroupRepository = $repository;
    }

    public function injectCredentialsGenerator(CredentialsGenerator $generator): void
    {
        $this->credentialsGenerator = $generator;
    }

    public function injectUserValidator(UserValidator $validator): void
    {
        $this->userValidator = $validator;
    }

    /**
     * Creates the user creation form (which initially is empty).
     *
     * @TYPO3\CMS\Extbase\Annotation\IgnoreValidation("user")
     */
    public function newAction(?FrontendUser $user = null, ?int $userGroup = null): void
    {
        $newUser = ($user instanceof FrontendUser) ? $user : GeneralUtility::makeInstance(FrontendUser::class);
        $this->view->assign('user', $newUser);
        $this->view->assign('selectedUserGroup', $userGroup);

        $userGroupSetting = $this->settings['groupsForNewUsers'] ?? null;
        $userGroupUids = \is_string($userGroupSetting) ? GeneralUtility::intExplode(',', $userGroupSetting, true) : [];
        $userGroups = $this->userGroupRepository->findByUids($userGroupUids);
        $this->view->assign('userGroups', $userGroups);

        $redirectUrl = GeneralUtility::_GP('redirect_url');
        if (\is_string($redirectUrl) && $redirectUrl !== '') {
            $this->view->assign('redirectUrl', $redirectUrl);
        }
    }

    public function initializeCreateAction(): void
    {
        if (!$this->arguments->hasArgument('user')) {
            return;
        }

        $userValidator = $this->userValidator;
        $userValidator->setSettings($this->settings);
        $this->arguments->getArgument('user')->setValidator($userValidator);
    }

    /**
     * Creates and persists a new user.
     *
     * Note: `$user` is optional in order to avoid a crash when someone is using a FE login form on the sane page
     * after creating a user with this action. (This will use the current URL as form target, causing the user to be
     * null as it had been sent via a POST request.)
     *
     * @throws \RuntimeException
     */
    public function createAction(?FrontendUser $user = null, ?int $userGroup = null): void
    {
        if (!$user instanceof FrontendUser) {
            return;
        }

        $plaintextPassword = $this->enrichUser($user, $userGroup);
        if (!\is_string($plaintextPassword)) {
            throw new \RuntimeException('Could not generate user credentials.', 1651673684);
        }

        $this->userRepository->add($user);
        $this->userRepository->persistAll();

        $this->afterCreate($user, $plaintextPassword);

        $redirectUrl = GeneralUtility::_POST('redirect_url');
        if (\is_string($redirectUrl) && $redirectUrl !== '') {
            $sanitizedUrl = GeneralUtility::sanitizeLocalUrl($redirectUrl);
            if ($sanitizedUrl !== '') {
                $this->redirectToUri($redirectUrl);
            }
        }
    }

    /**
     * This method will be executed as the last step of `createAction` (before the potential redirect).
     */
    abstract protected function afterCreate(FrontendUser $user, string $plaintextPassword): void;

    /**
     * Adds data from the configuration to the user before it can be saved.
     *
     * @return string the plaintext password, or null if no new password should be generated
     */
    private function enrichUser(FrontendUser $user, ?int $userGroupUid): ?string
    {
        $this->generateFullNameForUser($user);
        $this->credentialsGenerator->generateUsernameForUser($user);
        $password = $this->credentialsGenerator->generatePasswordForUser($user);

        $this->enrichWithPid($user);
        $this->enrichWithGroup($user, $userGroupUid);

        return $password;
    }

    protected function generateFullNameForUser(FrontendUser $user): void
    {
        if ($user->getName() !== '') {
            return;
        }

        $fullName = \trim($user->getFirstName() . ' ' . $user->getLastName());
        $user->setName($fullName);
    }

    private function enrichWithPid(FrontendUser $user): void
    {
        $pageUid = $this->settings['systemFolderForNewUsers'] ?? null;
        if (\is_numeric($pageUid)) {
            $user->setPid((int)$pageUid);
        }
    }

    private function enrichWithGroup(FrontendUser $user, ?int $userGroupUid): void
    {
        if (!\is_int($userGroupUid) || $userGroupUid < 1) {
            return;
        }

        $userGroupSetting = $this->settings['groupsForNewUsers'] ?? null;
        $userGroupUids = \is_string($userGroupSetting) ? GeneralUtility::intExplode(',', $userGroupSetting, true) : [];
        if (\in_array($userGroupUid, $userGroupUids, true)) {
            $group = $this->userGroupRepository->findByUid($userGroupUid);
            if ($group instanceof FrontendUserGroup) {
                $user->addUserGroup($group);
            }
        }
    }
}
