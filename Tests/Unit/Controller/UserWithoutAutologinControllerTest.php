<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUserGroup;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserGroupRepository;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController;
use OliverKlee\Onetimeaccount\Service\CredentialsGenerator;
use OliverKlee\Onetimeaccount\Validation\UserValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument as ExtbaseArgument;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController
 * @covers \OliverKlee\Onetimeaccount\Controller\AbstractUserController
 */
final class UserWithoutAutologinControllerTest extends UnitTestCase
{
    /**
     * @var UserWithoutAutologinController&MockObject&AccessibleObjectInterface
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $subject;

    /**
     * @var ObjectProphecy<TemplateView>
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $viewProphecy;

    /**
     * @var ObjectProphecy<FrontendUserRepository>
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $userRepositoryProphecy;

    /**
     * @var ObjectProphecy<FrontendUserGroupRepository>
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $userGroupRepositoryProphecy;

    /**
     * @var ObjectProphecy<PersistenceManagerInterface>
     */
    protected $persistenceManagerProphecy;

    /**
     * @var ObjectProphecy<CredentialsGenerator>
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $credentialsGeneratorProphecy;

    /**
     * @var ObjectProphecy<UserValidator>
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $userValidatorProphecy;

    /**
     * @var Arguments
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $controllerArguments;

    protected function setUp(): void
    {
        parent::setUp();

        // We need to create an accessible mock in order to be able to set the protected `view`.
        // We can drop the additional arguments to skip the original constructor once we drop support for TYPO3 V9.
        $this->subject
            = $this->getAccessibleMock(UserWithoutAutologinController::class, ['redirect', 'forward'], [], '', false);

        $this->viewProphecy = $this->prophesize(TemplateView::class);
        $view = $this->viewProphecy->reveal();
        $this->subject->_set('view', $view);

        $this->userRepositoryProphecy = $this->prophesize(FrontendUserRepository::class);
        $userRepository = $this->userRepositoryProphecy->reveal();
        $this->subject->injectFrontendUserRepository($userRepository);

        $this->userGroupRepositoryProphecy = $this->prophesize(FrontendUserGroupRepository::class);
        $userGroupRepository = $this->userGroupRepositoryProphecy->reveal();
        $this->subject->injectFrontendUserGroupRepository($userGroupRepository);

        $this->persistenceManagerProphecy = $this->prophesize(PersistenceManagerInterface::class);
        $persistenceManager = $this->persistenceManagerProphecy->reveal();
        $this->subject->injectPersistenceManager($persistenceManager);

        $this->credentialsGeneratorProphecy = $this->prophesize(CredentialsGenerator::class);
        $usernameGenerator = $this->credentialsGeneratorProphecy->reveal();
        $this->subject->injectCredentialsGenerator($usernameGenerator);

        $this->userValidatorProphecy = $this->prophesize(UserValidator::class);
        $userValidator = $this->userValidatorProphecy->reveal();
        $this->subject->injectUserValidator($userValidator);

        $this->controllerArguments = new Arguments();
        $this->subject->_set('arguments', $this->controllerArguments);
    }

    /**
     * @test
     */
    public function isActionController(): void
    {
        self::assertInstanceOf(ActionController::class, $this->subject);
    }

    /**
     * @test
     */
    public function newActionWithUserPassesUserToView(): void
    {
        $user = new FrontendUser();

        $this->viewProphecy->assign('user', $user)->shouldBeCalled();

        $this->subject->newAction($user);
    }

    /**
     * @test
     */
    public function newActionWithoutUserPassesVirginUserToView(): void
    {
        $this->viewProphecy->assign('user', Argument::type(FrontendUser::class))->shouldBeCalled();

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionWithNullUserPassesVirginUserToView(): void
    {
        $this->viewProphecy->assign('user', Argument::type(FrontendUser::class))->shouldBeCalled();

        $this->subject->newAction(null);
    }

    /**
     * @test
     */
    public function initializeCreateActionWithUserArgumentSetsUserValidatorWithSettings(): void
    {
        $user = new FrontendUser();
        $userArgument = new ExtbaseArgument('user', FrontendUser::class);
        $userArgument->setValue($user);
        $this->controllerArguments->addArgument($userArgument);

        $settings = ['fieldsToShow' => 'name,email', 'requiredFields' => 'email'];
        $this->subject->_set('settings', $settings);
        $this->userValidatorProphecy->setSettings($settings)->shouldBeCalled();

        $this->subject->initializeCreateAction();

        self::assertSame($this->userValidatorProphecy->reveal(), $userArgument->getValidator());
    }

    /**
     * @test
     */
    public function initializeCreateActionWithoutUserArgumentNotTouchesUserValidator(): void
    {
        $this->subject->_set('settings', []);

        $this->userValidatorProphecy->setSettings(Argument::any())->shouldNotBeCalled();

        $this->subject->initializeCreateAction();
    }

    /**
     * @test
     */
    public function createActionSetsUserPidFromSettings(): void
    {
        $systemFolderUid = 42;
        $this->subject->_set('settings', ['systemFolderForNewUsers' => (string)$systemFolderUid]);
        $user = new FrontendUser();
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('');

        $this->subject->createAction($user);

        self::assertSame($systemFolderUid, $user->getPid());
    }

    /**
     * @test
     */
    public function createActionSetsGroupsFromConfiguration(): void
    {
        $groupUid1 = 4;
        $group1 = new FrontendUserGroup();
        $group2 = new FrontendUserGroup();
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->userGroupRepositoryProphecy->findByUid($groupUid1)->willReturn($group1);
        $this->userGroupRepositoryProphecy->findByUid($groupUid2)->willReturn($group2);
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('');

        $user = new FrontendUser();
        $this->subject->createAction($user);

        self::assertContains($group1, $user->getUserGroup());
        self::assertContains($group1, $user->getUserGroup());
    }

    /**
     * @test
     */
    public function createActionGeneratesUsername(): void
    {
        $user = new FrontendUser();
        $this->credentialsGeneratorProphecy->generateUsernameForUser($user)->shouldBeCalled();
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any());

        $this->subject->createAction($user);
    }

    /**
     * @test
     */
    public function createActionGeneratesPassword(): void
    {
        $user = new FrontendUser();
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser($user)->shouldBeCalled();

        $this->subject->createAction($user);
    }

    /**
     * @test
     */
    public function createActionForUserWithFullNameAndFirstNameAndLastNameKeepsFullNameUnchanged(): void
    {
        $fullName = 'Max Performance';
        $user = new FrontendUser();
        $user->setName($fullName);
        $user->setFirstName('Mini');
        $user->setLastName('Slowness');
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any())->shouldBeCalled();
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->shouldBeCalled();

        $this->subject->createAction($user);

        self::assertSame($fullName, $user->getName());
    }

    /**
     * @test
     */
    public function createActionForUserWithFullNameAndFirstNameAndNoLastNameKeepsFullNameUnchanged(): void
    {
        $fullName = 'Max Performance';
        $user = new FrontendUser();
        $user->setName($fullName);
        $user->setFirstName('Mini');
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any())->shouldBeCalled();
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->shouldBeCalled();

        $this->subject->createAction($user);

        self::assertSame($fullName, $user->getName());
    }

    /**
     * @test
     */
    public function createActionForUserWithFullNameAndLastNameAndNoFirstNameKeepsFullNameUnchanged(): void
    {
        $fullName = 'Max Performance';
        $user = new FrontendUser();
        $user->setName($fullName);
        $user->setLastName('Slowness');
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any())->shouldBeCalled();
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->shouldBeCalled();

        $this->subject->createAction($user);

        self::assertSame($fullName, $user->getName());
    }

    /**
     * @test
     */
    public function createActionForUserWithoutFullNameAndFirstAndLastNameBuildsFullNameFromFirstAndLastName(): void
    {
        $user = new FrontendUser();
        $firstName = 'Mini';
        $user->setFirstName($firstName);
        $lastName = 'Slowness';
        $user->setLastName($lastName);
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any())->shouldBeCalled();
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->shouldBeCalled();

        $this->subject->createAction($user);

        $expectedFullName = $firstName . ' ' . $lastName;
        self::assertSame($expectedFullName, $user->getName());
    }

    /**
     * @test
     */
    public function createActionForUserWithFirstNameOnlySetsFullNameToFirstName(): void
    {
        $user = new FrontendUser();
        $firstName = 'Mini';
        $user->setFirstName($firstName);
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any())->shouldBeCalled();
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->shouldBeCalled();

        $this->subject->createAction($user);

        self::assertSame($firstName, $user->getName());
    }

    /**
     * @test
     */
    public function createActionForUserWithLastNameOnlySetsFullNameToLastName(): void
    {
        $user = new FrontendUser();
        $lastName = 'Slowness';
        $user->setLastName($lastName);
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any())->shouldBeCalled();
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->shouldBeCalled();

        $this->subject->createAction($user);

        self::assertSame($lastName, $user->getName());
    }

    /**
     * @test
     */
    public function createActionForUserWithNoNameAtAllKeepsEmptyFullyName(): void
    {
        $user = new FrontendUser();
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any())->shouldBeCalled();
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->shouldBeCalled();

        $this->subject->createAction($user);

        self::assertSame('', $user->getName());
    }

    /**
     * @test
     */
    public function createActionWithUserAddsProvidedUserToRepository(): void
    {
        $user = new FrontendUser();
        $this->userRepositoryProphecy->add($user)->shouldBeCalled();
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('');

        $this->subject->createAction($user);
    }

    /**
     * @test
     */
    public function createActionWithUserPersistsEverything(): void
    {
        $user = new FrontendUser();
        $this->persistenceManagerProphecy->persistAll()->shouldBeCalled();
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('');

        $this->subject->createAction($user);
    }

    /**
     * @test
     */
    public function createActionWithUserForFailedPasswordGenerationThrowsException(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Could not generate user credentials.');
        $this->expectExceptionCode(1651673684);

        $user = new FrontendUser();
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser($user)->willReturn(null);

        $this->subject->createAction($user);
    }

    /**
     * @test
     */
    public function createActionWithNullUserNotAddsAnythingToRepository(): void
    {
        $this->userRepositoryProphecy->add(Argument::any())->shouldNotBeCalled();

        $this->subject->createAction(null);
    }

    /**
     * @test
     */
    public function createActionWithoutUserNotAddsAnythingToRepository(): void
    {
        $this->userRepositoryProphecy->add(Argument::any())->shouldNotBeCalled();

        $this->subject->createAction();
    }

    /**
     * @test
     */
    public function createActionWithNullUserNotPersistsAnything(): void
    {
        $this->persistenceManagerProphecy->persistAll()->shouldNotBeCalled();

        $this->subject->createAction(null);
    }

    /**
     * @test
     */
    public function createActionWithoutUserNotPersistsAnything(): void
    {
        $this->persistenceManagerProphecy->persistAll()->shouldNotBeCalled();

        $this->subject->createAction();
    }
}
