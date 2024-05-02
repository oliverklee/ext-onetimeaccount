<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var FrontendUserAuthentication&MockObject
     */
    private MockObject $userMock;

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
        $this->userMock = $this->createMock(FrontendUserAuthentication::class);

        $frontEndController = $this->createMock(TypoScriptFrontendController::class);
        $frontEndController->fe_user = $this->userMock;
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

        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

        $this->userMock->expects(self::once())
            ->method('setAndSaveSessionData')
            ->with('onetimeaccountUserUid', $userUid);

        $this->subject->createAction($user);
    }
}
