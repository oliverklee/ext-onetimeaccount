<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Service;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Authentication\LoginType;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Creates a FE session for the given user.
 */
class Autologin implements SingletonInterface
{
    /**
     * @var non-empty-string
     */
    public const ONETIMEACCOUNT_SESSION_MARKER = 'onetimeaccount';

    public function createSessionForUser(FrontendUser $user, string $plaintextPassword): void
    {
        $userAuthentication = $this->getFrontendUserAuthentication();

        $_POST['user'] = $user->getUsername();
        $_POST['pass'] = $plaintextPassword;
        $_POST['logintype'] = LoginType::LOGIN;

        $userAuthentication->checkPid = false;
        $userAuthentication->start();
        $userAuthentication->setKey('user', self::ONETIMEACCOUNT_SESSION_MARKER, true);
        $userAuthentication->storeSessionData();
    }

    /**
     * @throws \RuntimeException
     */
    private function getFrontendUserAuthentication(): FrontendUserAuthentication
    {
        $authentication = $this->getFrontEndController()->fe_user;
        if (!$authentication instanceof FrontendUserAuthentication) {
            throw new \RuntimeException('No frontend user authentication found.', 1651593718);
        }

        return $authentication;
    }

    /**
     * @throws \RuntimeException
     */
    private function getFrontEndController(): TypoScriptFrontendController
    {
        $frontEndController = $GLOBALS['TSFE'] ?? null;
        if (!$frontEndController instanceof TypoScriptFrontendController) {
            throw new \RuntimeException('No frontend controller found.', 1651593678);
        }
        return $frontEndController;
    }
}
