<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUserGroup;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserGroupRepository;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Oelib\Testing\CacheNullifyer;
use OliverKlee\Onetimeaccount\Controller\AbstractUserController;
use OliverKlee\Onetimeaccount\Service\CredentialsGenerator;
use OliverKlee\Onetimeaccount\Tests\Unit\Controller\Fixtures\XclassFrontendUser;
use OliverKlee\Onetimeaccount\Validation\UserValidator;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument as ExtbaseArgument;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @template C of AbstractUserController
 */
abstract class AbstractUserControllerTest extends UnitTestCase
{
    /**
     * @var non-empty-string
     */
    protected const SITE_URL = 'https://www.example.com';

    /**
     * @var bool
     */
    protected $resetSingletonInstances = true;

    /**
     * @var C&MockObject&AccessibleObjectInterface
     */
    protected $subject;

    /**
     * @var ObjectProphecy<TemplateView>
     */
    private $viewProphecy;

    /**
     * @var ObjectProphecy<FrontendUserRepository>
     */
    private $userRepositoryProphecy;

    /**
     * @var ObjectProphecy<FrontendUserGroupRepository>
     */
    private $userGroupRepositoryProphecy;

    /**
     * @var ObjectProphecy<CredentialsGenerator>
     */
    protected $credentialsGeneratorProphecy;

    /**
     * @var ObjectProphecy<UserValidator>
     */
    private $userValidatorProphecy;

    /**
     * @var Arguments
     */
    private $controllerArguments;

    protected function setUpAndInjectSharedDependencies(): void
    {
        $this->viewProphecy = $this->prophesize(TemplateView::class);
        $view = $this->viewProphecy->reveal();
        $this->subject->_set('view', $view);

        $this->userRepositoryProphecy = $this->prophesize(FrontendUserRepository::class);
        $userRepository = $this->userRepositoryProphecy->reveal();
        $this->subject->injectFrontendUserRepository($userRepository);

        $this->userGroupRepositoryProphecy = $this->prophesize(FrontendUserGroupRepository::class);
        $userGroupRepository = $this->userGroupRepositoryProphecy->reveal();
        $this->subject->injectFrontendUserGroupRepository($userGroupRepository);

        $this->credentialsGeneratorProphecy = $this->prophesize(CredentialsGenerator::class);
        $credentialsGenerator = $this->credentialsGeneratorProphecy->reveal();
        $this->subject->injectCredentialsGenerator($credentialsGenerator);

        $this->userValidatorProphecy = $this->prophesize(UserValidator::class);
        $userValidator = $this->userValidatorProphecy->reveal();
        $this->subject->injectUserValidator($userValidator);

        $this->controllerArguments = new Arguments();
        $this->subject->_set('arguments', $this->controllerArguments);
    }

    protected function tearDown(): void
    {
        // @phpstan-ignore-next-line We know that the necessary array keys exist.
        unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FrontendUser::class]);
        $this->resetRequestData();
        parent::tearDown();
    }

    protected function setDummyRequestData(): void
    {
        $this->resetRequestData();
        GeneralUtility::setIndpEnv('TYPO3_SITE_URL', self::SITE_URL);
        GeneralUtility::setIndpEnv('TYPO3_REQUEST_HOST', 'https://www.example.com');
    }

    private function resetRequestData(): void
    {
        $_GET = [];
        $_POST = [];
        (new CacheNullifyer())->flushMakeInstanceCache();
        GeneralUtility::flushInternalRuntimeCaches();
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
        $this->viewProphecy->assign('selectedUserGroup', Argument::any())->shouldBeCalled();

        $this->subject->newAction($user);
    }

    /**
     * @test
     */
    public function newActionWithoutUserPassesVirginUserToView(): void
    {
        $this->viewProphecy->assign('user', Argument::type(FrontendUser::class))->shouldBeCalled();
        $this->viewProphecy->assign('selectedUserGroup', Argument::any())->shouldBeCalled();

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionWithNullUserPassesVirginUserToView(): void
    {
        $this->viewProphecy->assign('user', Argument::type(FrontendUser::class))->shouldBeCalled();
        $this->viewProphecy->assign('selectedUserGroup', Argument::any())->shouldBeCalled();

        $this->subject->newAction(null);
    }

    /**
     * @test
     */
    public function newActionWithPassesProvidedUserGroupUidToView(): void
    {
        $userGroupUid = 5;
        $this->viewProphecy->assign('user', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('selectedUserGroup', $userGroupUid)->shouldBeCalled();

        $this->subject->newAction(null, $userGroupUid);
    }

    /**
     * @test
     */
    public function newActionWithNullUserGroupPassesNullUserGroupToView(): void
    {
        $this->viewProphecy->assign('user', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('selectedUserGroup', null)->shouldBeCalled();

        $this->subject->newAction(null, null);
    }

    /**
     * @test
     */
    public function newActionWithMissingUserGroupPassesNullUserGroupToView(): void
    {
        $this->viewProphecy->assign('user', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('selectedUserGroup', null)->shouldBeCalled();

        $this->subject->newAction(null, null);
    }

    /**
     * @test
     */
    public function newActionWithoutUserPassesCanPassVirginSubclassedUserToView(): void
    {
        // @phpstan-ignore-next-line We know that the necessary array keys exist.
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FrontendUser::class] = ['className' => XclassFrontendUser::class];

        $this->viewProphecy->assign('user', Argument::type(XclassFrontendUser::class))->shouldBeCalled();
        $this->viewProphecy->assign('selectedUserGroup', Argument::any())->shouldBeCalled();

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionPassesConfiguredUserGroupsToView(): void
    {
        $groupUid1 = 1;
        $groupUid2 = 2;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $userGroups = $this->prophesize(QueryResultInterface::class)->reveal();
        $this->userGroupRepositoryProphecy->findByUids([$groupUid1, $groupUid2])->willReturn($userGroups);

        $this->viewProphecy->assign('user', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('selectedUserGroup', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('userGroups', $userGroups)->shouldBeCalled();

        $this->subject->newAction();
    }

    /**
     * @return array<string, array{0: ''|null}>
     */
    public function emptyParameterDataProvider(): array
    {
        return [
            'empty string' => [''],
            'null' => [null],
        ];
    }

    /**
     * @test
     *
     * @dataProvider emptyParameterDataProvider
     */
    public function newActionWithEmptyRedirectUrlInGetNotPassesRedirectUrlToView(?string $redirectUrl): void
    {
        $_GET['redirect_url'] = $redirectUrl;

        $this->viewProphecy->assign('user', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('selectedUserGroup', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('redirectUrl', Argument::any())->shouldNotBeCalled();

        $this->subject->newAction();
    }

    /**
     * @test
     *
     * @dataProvider emptyParameterDataProvider
     */
    public function newActionWithEmptyRedirectUrlInPostNotPassesRedirectUrlToView(?string $redirectUrl): void
    {
        $_POST['redirect_url'] = $redirectUrl;

        $this->viewProphecy->assign('user', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('selectedUserGroup', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('redirectUrl', Argument::any())->shouldNotBeCalled();

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionWithRedirectUrlInSiteInGetPassesRedirectUrlToView(): void
    {
        $redirectUrl = 'https://example.com/';
        $_GET['redirect_url'] = $redirectUrl;

        $this->viewProphecy->assign('user', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('selectedUserGroup', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('redirectUrl', $redirectUrl)->shouldBeCalled();

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionWithRedirectUrlInSiteInPostPassesRedirectUrlToView(): void
    {
        $redirectUrl = 'https://example.com/';
        $_POST['redirect_url'] = $redirectUrl;

        $this->viewProphecy->assign('user', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('selectedUserGroup', Argument::any())->shouldBeCalled();
        $this->viewProphecy->assign('redirectUrl', $redirectUrl)->shouldBeCalled();

        $this->subject->newAction();
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
    public function createActionWithAllowedExistentGroupUidSetsGivenGroup(): void
    {
        $groupUid1 = 4;
        $group1 = new FrontendUserGroup();
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->userGroupRepositoryProphecy->findByUid($groupUid1)->willReturn($group1);
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('');

        $user = new FrontendUser();
        $this->subject->createAction($user, $groupUid1);

        self::assertCount(1, $user->getUserGroup());
        self::assertContains($group1, $user->getUserGroup());
    }

    /**
     * @test
     */
    public function createActionWithAllowedInexistentGroupUidNotSetsAnyGroup(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->userGroupRepositoryProphecy->findByUid($groupUid1)->willReturn(null);
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('');

        $user = new FrontendUser();
        $this->subject->createAction($user, $groupUid1);

        self::assertCount(0, $user->getUserGroup());
    }

    /**
     * @test
     */
    public function createActionWithNotAllowedGroupUidNotSetsAnyGroup(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('');

        $user = new FrontendUser();
        $this->subject->createAction($user, 123);

        self::assertCount(0, $user->getUserGroup());
    }

    /**
     * @test
     */
    public function createActionWithNullGroupUidNotSetsAnyGroup(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('');

        $user = new FrontendUser();
        $this->subject->createAction($user, null);

        self::assertCount(0, $user->getUserGroup());
    }

    /**
     * @test
     */
    public function createActionWithZeroGroupUidNotSetsAnyGroup(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('');

        $user = new FrontendUser();
        $this->subject->createAction($user, 0);

        self::assertCount(0, $user->getUserGroup());
    }

    /**
     * @test
     */
    public function createActionWithNegativeGroupUidNotSetsAnyGroup(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('');

        $user = new FrontendUser();
        $this->subject->createAction($user, -1);

        self::assertCount(0, $user->getUserGroup());
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
        $this->userRepositoryProphecy->persistAll();
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
        $this->userRepositoryProphecy->add(Argument::any())->shouldBeCalled();
        $this->userRepositoryProphecy->persistAll()->shouldBeCalled();
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
        $this->userRepositoryProphecy->persistAll()->shouldNotBeCalled();

        $this->subject->createAction(null);
    }

    /**
     * @test
     */
    public function createActionWithoutUserNotPersistsAnything(): void
    {
        $this->userRepositoryProphecy->persistAll()->shouldNotBeCalled();

        $this->subject->createAction();
    }

    /**
     * @test
     *
     * @dataProvider emptyParameterDataProvider
     */
    public function createActionWithUserWithEmptyRedirectUrlInPostNotRedirects(?string $redirectUrl): void
    {
        $_POST['redirect_url'] = $redirectUrl;

        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('');
        $this->subject->expects(self::never())->method('redirectToUri');

        $this->subject->createAction(new FrontendUser());
    }

    /**
     * @test
     */
    public function createActionWithUserWithLocalRedirectUrlInPostRedirectsToRedirectUrl(): void
    {
        $redirectUrl = self::SITE_URL;
        $_POST['redirect_url'] = $redirectUrl;

        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('');
        $this->subject->expects(self::once())->method('redirectToUri')->with($redirectUrl);

        $this->subject->createAction(new FrontendUser());
    }

    /**
     * @test
     */
    public function createActionWithUserWithExternalRedirectUrlNotRedirects(): void
    {
        $_POST['redirect_url'] = 'https://www.oliverklee.de/';

        $this->credentialsGeneratorProphecy->generateUsernameForUser(Argument::any());
        $this->credentialsGeneratorProphecy->generatePasswordForUser(Argument::any())->willReturn('');
        $this->subject->expects(self::never())->method('redirectToUri');

        $this->subject->createAction(new FrontendUser());
    }

    /**
     * @test
     */
    public function createActionWithoutUserWithRedirectUrlInSiteNotRedirects(): void
    {
        $_POST['redirect_url'] = self::SITE_URL;

        $this->subject->expects(self::never())->method('redirectToUri');

        $this->subject->createAction();
    }
}
