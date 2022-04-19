<?php

declare(strict_types=1);

namespace OliverKlee\OneTimeAccount\Controller;

use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Plugin for creating a front-end user, but without the autologin.
 */
class UserWithoutAutologinController extends ActionController
{
    /**
     * Creates the user creation form (which initially is empty).
     */
    public function newAction(): void
    {
    }
}
