<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;

/**
 * Plugin for creating a front-end user, but without the autologin.
 */
class UserWithoutAutologinController extends AbstractUserController
{
    /**
     * Does nothing (yet).
     */
    protected function afterCreate(FrontendUser $user, string $plaintextPassword): void
    {
    }
}
