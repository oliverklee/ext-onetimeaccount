<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUserGroup;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserGroupRepository;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Onetimeaccount\Domain\Model\Captcha;
use OliverKlee\Onetimeaccount\Service\CaptchaFactory;
use OliverKlee\Onetimeaccount\Service\CredentialsGenerator;
use OliverKlee\Onetimeaccount\Validation\CaptchaValidator;
use OliverKlee\Onetimeaccount\Validation\UserValidator;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Validation\Validator\ConjunctionValidator;

/**
 * Base class to implement most of the functionality of the plugin except for the specifics of what should
 * happen after a user has been created (autologin or storing the user UID in the session).
 *
 * @internal Only use the concrete subclasses of this class.
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

    /**
     * @var CaptchaValidator
     */
    protected $captchaValidator;

    /**
     * @var CaptchaFactory
     */
    protected $captchaFactory;

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

    public function injectCaptchaValidator(CaptchaValidator $validator): void
    {
        $this->captchaValidator = $validator;
    }

    public function injectCaptchaFactory(CaptchaFactory $factory): void
    {
        $this->captchaFactory = $factory;
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
        if ($userGroupUids !== []) {
            $userGroups = $this->userGroupRepository->findByUids($userGroupUids);
            $this->view->assign('userGroups', $userGroups);
        }

        $captchaSetting = $this->settings['captcha'] ?? 0;
        $isCaptchaEnabled = (\is_string($captchaSetting) || \is_int($captchaSetting)) ? (bool)$captchaSetting : false;

        if ($isCaptchaEnabled) {
            $this->view->assign('captcha', $this->captchaFactory->generateChallenge());
        }

        $redirectUrl = GeneralUtility::_GP('redirect_url');
        if (\is_string($redirectUrl) && $redirectUrl !== '') {
            $this->view->assign('redirectUrl', $redirectUrl);
        }
    }

    public function initializeCreateAction(): void
    {
        if ($this->arguments->hasArgument('user')) {
            $conjunctionUserValidator = new ConjunctionValidator();
            $conjunctionUserValidator->addValidator($this->arguments->getArgument('user')->getValidator());
            $userValidator = $this->userValidator;
            $userValidator->setSettings($this->settings);
            $conjunctionUserValidator->addValidator($userValidator);
            $this->arguments->getArgument('user')->setValidator($conjunctionUserValidator);
        }
        if ($this->arguments->hasArgument('captcha')) {
            $conjunctionCaptchaValidator = new ConjunctionValidator();
            $conjunctionCaptchaValidator->addValidator($this->arguments->getArgument('captcha')->getValidator());
            $captchaValidator = $this->captchaValidator;
            $captchaValidator->setSettings($this->settings);
            $conjunctionCaptchaValidator->addValidator($captchaValidator);
            $this->arguments->getArgument('captcha')->setValidator($conjunctionCaptchaValidator);
        }
    }

    /**
     * Creates and persists a new user.
     *
     * The arguments are optional in order to avoid a crash when someone is using a FE login form on the sane page
     * after creating a user with this action. (This will use the current URL as form target, causing the user to be
     * null as it had been sent via a POST request.)
     *
     * @throws \RuntimeException
     */
    public function createAction(?FrontendUser $user = null, ?int $userGroup = null, ?Captcha $captcha = null): void
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
     * Enriches the user with a username, a password, a full name, a PID and a group.
     *
     * Also sets the last login date to now.
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
        $now = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'iso');
        \assert(\is_string($now));
        $user->setLastLogin(new \DateTime($now));

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
        $userGroupSetting = $this->settings['groupsForNewUsers'] ?? null;
        $userGroupUids = \is_string($userGroupSetting) ? GeneralUtility::intExplode(',', $userGroupSetting, true) : [];
        if (\is_int($userGroupUid) && $userGroupUid >= 1 && \in_array($userGroupUid, $userGroupUids, true)) {
            $group = $this->userGroupRepository->findByUid($userGroupUid);
            if ($group instanceof FrontendUserGroup) {
                $user->addUserGroup($group);
            }
        }

        if ($userGroupUids !== [] && $user->getUserGroup()->count() === 0) {
            $userGroups = $this->userGroupRepository->findByUids($userGroupUids);
            foreach ($userGroups as $userGroup) {
                $user->addUserGroup($userGroup);
            }
        }
    }
}
