<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Plugin for creating a front-end user and storing its UID in a FE session. This plugin has no autologin, though.
 */
class UserWithoutAutologinController extends AbstractUserController
{
    /**
     * Stores the UID of the created user in the FE session.
     */
    protected function afterCreate(FrontendUser $user, string $plaintextPassword): void
    {
        $frontEndAuthentication = $this->getFrontendUserAuthentication();
        $frontEndAuthentication->setAndSaveSessionData('onetimeaccountUserUid', $user->getUid());
    }

    private function getFrontendUserAuthentication(): FrontendUserAuthentication
    {
        $controller = $GLOBALS['TSFE'] ?? null;
        \assert($controller instanceof TypoScriptFrontendController);
        $user = $controller->fe_user;
        \assert($user instanceof FrontendUserAuthentication);

        return $user;
    }
}
