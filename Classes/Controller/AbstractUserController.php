<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
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

    public function injectFrontendUserRepository(FrontendUserRepository $repository): void
    {
        $this->userRepository = $repository;
    }

    /**
     * Creates the user creation form (which initially is empty).
     */
    public function newAction(?FrontendUser $user = null): void
    {
        $newUser = ($user instanceof FrontendUser) ? $user : new FrontendUser();

        $this->view->assign('user', $newUser);
    }

    public function createAction(FrontendUser $user): string
    {
        $this->enrichUser($user);
        $this->userRepository->add($user);

        return 'User has been created';
    }

    /**
     * Adds data from the configuration to the user before it can be saved.
     */
    private function enrichUser(FrontendUser $user): void
    {
        $settings = $this->settings;
        $pageUid = $settings['systemFolderForNewUsers'] ?? 0;
        if (\is_numeric($pageUid)) {
            $user->setPid((int)$pageUid);
        }
    }
}
