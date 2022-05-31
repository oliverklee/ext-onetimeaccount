<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Service;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Onetimeaccount\Service\Autologin;
use Psr\Log\NullLogger;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Service\Autologin
 */
final class AutologinTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = ['typo3conf/ext/feuserextrafields', 'typo3conf/ext/onetimeaccount'];

    protected $coreExtensionsToLoad = ['extbase'];

    /**
     * @var Autologin
     */
    private $subject;

    /**
     * @var FrontendUserRepository
     */
    private $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->userRepository = $objectManager->get(FrontendUserRepository::class);

        $this->setUpFakeFrontEnd();

        $this->subject = new Autologin();
    }

    private function setUpFakeFrontEnd(): void
    {
        $frontEndController = $this->getMockBuilder(TypoScriptFrontendController::class)
            ->disableOriginalConstructor()->getMock();

        $userAuthentication = new FrontendUserAuthentication();
        $userAuthentication->setLogger(new NullLogger());

        $frontEndController->fe_user = $userAuthentication;
        $GLOBALS['TSFE'] = $frontEndController;
    }

    /**
     * @test
     */
    public function createSessionForUserProvidesFrontEndControllerWithFilledAuthentication(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/User.xml');
        $user = $this->userRepository->findByUid(1);
        self::assertInstanceOf(FrontendUser::class, $user);
        $password = 'max-has-a-password';

        $this->subject->createSessionForUser($user, $password);

        $frontEndController = $GLOBALS['TSFE'];
        self::assertInstanceOf(TypoScriptFrontendController::class, $frontEndController);
        $authentication = $frontEndController->fe_user;
        self::assertInstanceOf(FrontendUserAuthentication::class, $authentication);
        $userDataFromAuthentication = $authentication->user;
        self::assertIsArray($userDataFromAuthentication);
        self::assertSame(1, $userDataFromAuthentication['uid']);
        self::assertSame('max', $userDataFromAuthentication['username']);
    }

    /**
     * @test
     */
    public function createSessionForUserSetsOneTimeAccountFlagInSession(): void
    {
        $this->importDataSet(__DIR__ . '/Fixtures/User.xml');
        $user = $this->userRepository->findByUid(1);
        self::assertInstanceOf(FrontendUser::class, $user);
        $password = 'max-has-a-password';

        $this->subject->createSessionForUser($user, $password);

        $frontEndController = $GLOBALS['TSFE'];
        self::assertInstanceOf(TypoScriptFrontendController::class, $frontEndController);
        $authentication = $frontEndController->fe_user;
        self::assertInstanceOf(FrontendUserAuthentication::class, $authentication);

        self::assertTrue($authentication->getKey('user', 'onetimeaccount'));
    }
}
