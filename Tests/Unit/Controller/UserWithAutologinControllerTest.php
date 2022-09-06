<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Onetimeaccount\Controller\UserWithAutologinController;
use OliverKlee\Onetimeaccount\Service\Autologin;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;

/**
 * @extends AbstractUserControllerTest<UserWithAutologinController>
 *
 * @covers \OliverKlee\Onetimeaccount\Controller\UserWithAutologinController
 * @covers \OliverKlee\Onetimeaccount\Controller\AbstractUserController
 */
final class UserWithAutologinControllerTest extends AbstractUserControllerTest
{
    /**
     * @var ObjectProphecy<Autologin>
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $autologinProphecy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->setDummyRequestData();

        // We need to create an accessible mock in order to be able to set the protected `view`.
        // We can drop the additional arguments to skip the original constructor once we drop support for TYPO3 V9.
        $this->subject = $this->getAccessibleMock(
            UserWithAutologinController::class,
            ['redirect', 'forward', 'redirectToUri'],
            [],
            '',
            false
        );

        $this->setUpAndInjectSharedDependencies();

        $this->autologinProphecy = $this->prophesize(Autologin::class);
        $autologinProphecy = $this->autologinProphecy->reveal();
        $this->subject->injectAutoLogin($autologinProphecy);
    }

    /**
     * @test
     */
    public function createActionWithUserCreatesSessionWithGeneratedPlainTextPassword(): void
    {
        $user = new FrontendUser();
        $hashedPassword = 'hashed-password';
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser($user)->willReturn($hashedPassword);
        $this->autologinProphecy->createSessionForUser($user, $hashedPassword)->shouldBeCalled();

        $this->subject->createAction($user);
    }
}
