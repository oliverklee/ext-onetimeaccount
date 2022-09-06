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
        $frontEndAuthentication = $this->getFrontEndAuthentication();
        $frontEndAuthentication->setAndSaveSessionData('onetimeaccountUserUid', $user->getUid());
    }

    private function getFrontEndAuthentication(): FrontendUserAuthentication
    {
        $controller = $GLOBALS['TSFE'] ?? null;
        if (!$controller instanceof TypoScriptFrontendController) {
            throw new \RuntimeException('No frontend found.', 1662482002);
        }

        $user = $controller->fe_user;
        if (!$user instanceof FrontendUserAuthentication) {
            throw new \RuntimeException('No frontend user found.', 1662482058);
        }

        return $user;
    }
}
