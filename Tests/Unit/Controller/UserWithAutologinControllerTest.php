<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Onetimeaccount\Controller\UserWithAutologinController;
use OliverKlee\Onetimeaccount\Service\Autologin;
use PHPUnit\Framework\MockObject\MockObject;

/**
 * @extends AbstractUserControllerTest<UserWithAutologinController>
 *
 * @covers \OliverKlee\Onetimeaccount\Controller\UserWithAutologinController
 * @covers \OliverKlee\Onetimeaccount\Controller\AbstractUserController
 */
final class UserWithAutologinControllerTest extends AbstractUserControllerTest
{
    /**
     * @var Autologin&MockObject
     */
    private $autologinMock;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setDummyRequestData();

        // We need to create an accessible mock in order to be able to set the protected `view`.
        $this->subject = $this->getAccessibleMock(
            UserWithAutologinController::class,
            ['redirect', 'forward', 'redirectToUri']
        );

        $this->setUpAndInjectSharedDependencies();

        $this->autologinMock = $this->createMock(Autologin::class);
        $this->subject->injectAutoLogin($this->autologinMock);
    }

    /**
     * @test
     */
    public function createActionWithUserCreatesSessionWithGeneratedPlainTextPassword(): void
    {
        $user = new FrontendUser();
        $hashedPassword = 'hashed-password';
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with($user)->willReturn($hashedPassword);
        $this->autologinMock->expects(self::once())->method('createSessionForUser')->with($user, $hashedPassword);

        $this->subject->createAction($user);
    }
}
