<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Base class to implement most of the functionality of the plugin except for the specifics of what should
 * happen after a user has been created (autologin or storing the user UID in the session).
 */
abstract class AbstractUserController extends ActionController
{
    /**
     * Creates the user creation form (which initially is empty).
     */
    public function newAction(): void
    {
    }
}
