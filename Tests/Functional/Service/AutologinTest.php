<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Service;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Oelib\Testing\TestingFramework;
use OliverKlee\Onetimeaccount\Service\Autologin;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Service\Autologin
 */
final class AutologinTest extends FunctionalTestCase
{
    protected array $testExtensionsToLoad = [
        'oliverklee/feuserextrafields',
        'oliverklee/oelib',
        'oliverklee/onetimeaccount',
    ];

    protected array $coreExtensionsToLoad = ['extbase', 'fluid'];

    private Autologin $subject;
    private FrontendUserRepository $userRepository;
    private TestingFramework $testingFramework;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testingFramework = new TestingFramework('tx_onetimeaccount');
        $this->testingFramework->createFakeFrontEnd($this->testingFramework->createFrontEndPage());

        /** @var FrontendUserRepository $userRepository */
        $userRepository = $this->get(FrontendUserRepository::class);
        $this->userRepository = $userRepository;

        $this->subject = new Autologin();
    }

    protected function tearDown(): void
    {
        $this->testingFramework->cleanUpWithoutDatabase();

        parent::tearDown();
    }

    /**
     * @test
     */
    public function createSessionForUserProvidesFrontEndControllerWithFilledAuthentication(): void
    {
        $this->importCSVDataSet(__DIR__ . '/Fixtures/User.csv');
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
        $this->importCSVDataSet(__DIR__ . '/Fixtures/User.csv');
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
