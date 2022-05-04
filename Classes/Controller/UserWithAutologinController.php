<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Onetimeaccount\Service\Autologin;

/**
 * Plugin for creating a front-end user and directly logging it in.
 */
class UserWithAutologinController extends AbstractUserController
{
    /**
     * @var Autologin
     */
    protected $autologin;

    public function injectAutoLogin(Autologin $autologin): void
    {
        $this->autologin = $autologin;
    }

    /**
     * Logs the created user in.
     */
    protected function afterCreate(FrontendUser $user, string $plaintextPassword): void
    {
        $this->autologin->createSessionForUser($user, $plaintextPassword);
    }
}
