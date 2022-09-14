<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Controller;

use OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Note: This test should be a unit test, but `GeneralUtility::flushInternalRuntimeCaches` currently cannot flush
 * the class name cache yet.
 *
 * @extends AbstractUserControllerTest<UserWithoutAutologinController>
 *
 * @covers \OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController
 * @covers \OliverKlee\Onetimeaccount\Controller\AbstractUserController
 */
final class UserWithoutAutologinControllerTest extends AbstractUserControllerTest
{
    /**
     * @var ObjectProphecy<FrontendUserAuthentication>
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $userProphecy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setDummyRequestData();

        $this->setUpFakeFrontEnd();

        // We need to create an accessible mock in order to be able to set the protected `view`.
        // We can drop the additional arguments to skip the original constructor once we drop support for TYPO3 V9.
        $this->subject = $this->getAccessibleMock(
            UserWithoutAutologinController::class,
            ['redirect', 'forward', 'redirectToUri'],
            [],
            '',
            false
        );

        $this->setUpAndInjectSharedDependencies();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TSFE']);
        parent::tearDown();
    }

    private function setUpFakeFrontEnd(): void
    {
        $this->userProphecy = $this->prophesize(FrontendUserAuthentication::class);
        $user = $this->userProphecy->reveal();

        $frontEndController = $this->prophesize(TypoScriptFrontendController::class)->reveal();
        $frontEndController->fe_user = $user;
        $GLOBALS['TSFE'] = $frontEndController;
    }
}
