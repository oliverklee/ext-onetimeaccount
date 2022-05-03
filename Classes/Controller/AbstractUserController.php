<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUserGroup;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserGroupRepository;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Onetimeaccount\Service\CredentialsGenerator;
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

    /**
     * Creates the user creation form (which initially is empty).
     */
    public function newAction(?FrontendUser $user = null): void
    {
        $newUser = ($user instanceof FrontendUser) ? $user : new FrontendUser();

        $this->view->assign('user', $newUser);
    }

    public function createAction(?FrontendUser $user = null): string
    {
        if (!$user instanceof FrontendUser) {
            return '';
        }

        $this->enrichUser($user);
        $this->userRepository->add($user);

        return 'User has been created';
    }

    /**
     * Adds data from the configuration to the user before it can be saved.
     */
    private function enrichUser(FrontendUser $user): void
    {
        $this->enrichWithPid($user);
        $this->enrichWithGroups($user);
        $this->credentialsGenerator->generateUsernameForUser($user);
    }

    private function enrichWithPid(FrontendUser $user): void
    {
        $pageUid = $this->settings['systemFolderForNewUsers'] ?? null;
        if (\is_numeric($pageUid)) {
            $user->setPid((int)$pageUid);
        }
    }

    private function enrichWithGroups(FrontendUser $user): void
    {
        $userGroupSetting = $this->settings['groupsForNewUsers'] ?? null;
        $userGroupUids = \is_string($userGroupSetting) ? GeneralUtility::intExplode(',', $userGroupSetting, true) : [];
        foreach ($userGroupUids as $uid) {
            $group = $this->userGroupRepository->findByUid($uid);
            if ($group instanceof FrontendUserGroup) {
                $user->addUserGroup($group);
            }
        }
    }
}
