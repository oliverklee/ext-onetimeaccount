<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * @extends AbstractUserControllerTest<UserWithoutAutologinController>
 *
 * @covers \OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController
 * @covers \OliverKlee\Onetimeaccount\Controller\AbstractUserController
 */
final class UserWithoutAutologinControllerTest extends AbstractUserControllerTest
{
    /**
     * @var ObjectProphecy<FrontendUserAuthentication>
     */
    private $userProphecy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setDummyRequestData();

        $this->setUpFakeFrontEnd();

        // We need to create an accessible mock in order to be able to set the protected `view`.
        $this->subject = $this->getAccessibleMock(
            UserWithoutAutologinController::class,
            ['redirect', 'forward', 'redirectToUri']
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

    /**
     * @test
     */
    public function createActionStoresUidOfNewUserInSession(): void
    {
        $userUid = 42;
        $user = new FrontendUser();
        $user->_setProperty('uid', $userUid);

        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('hashed-password');

        $this->userProphecy->setAndSaveSessionData('onetimeaccountUserUid', $userUid)->shouldBeCalled();

        $this->subject->createAction($user);
    }
}
