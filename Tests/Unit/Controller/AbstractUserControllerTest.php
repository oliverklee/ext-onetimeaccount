<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUserGroup;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserGroupRepository;
use OliverKlee\FeUserExtraFields\Domain\Repository\FrontendUserRepository;
use OliverKlee\Oelib\Testing\CacheNullifyer;
use OliverKlee\Onetimeaccount\Controller\AbstractUserController;
use OliverKlee\Onetimeaccount\Domain\Model\Captcha;
use OliverKlee\Onetimeaccount\Service\CaptchaFactory;
use OliverKlee\Onetimeaccount\Service\CredentialsGenerator;
use OliverKlee\Onetimeaccount\Tests\Unit\Controller\Fixtures\TestingQueryResult;
use OliverKlee\Onetimeaccount\Tests\Unit\Controller\Fixtures\XclassFrontendUser;
use OliverKlee\Onetimeaccount\Validation\CaptchaValidator;
use OliverKlee\Onetimeaccount\Validation\UserValidator;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Mvc\Controller\Argument as ExtbaseArgument;
use TYPO3\CMS\Extbase\Mvc\Controller\Arguments;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @template C of AbstractUserController
 *
 * @internal Only use the concrete subclasses of this class.
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
     * @var TemplateView&MockObject
     */
    private $viewMock;

    /**
     * @var FrontendUserRepository&MockObject
     */
    private $userRepositoryMock;

    /**
     * @var FrontendUserGroupRepository&MockObject
     */
    protected $userGroupRepositoryMock;

    /**
     * @var CredentialsGenerator&MockObject
     */
    protected $credentialsGeneratorMock;

    /**
     * @var UserValidator&MockObject
     */
    private $userValidatorMock;

    /**
     * @var CaptchaFactory&MockObject
     */
    private $captchaFactoryMock;

    /**
     * @var CaptchaValidator&MockObject
     */
    private $captchaValidatorMock;

    /**
     * @var Arguments
     */
    private $controllerArguments;

    protected function setUpAndInjectSharedDependencies(): void
    {
        $this->viewMock = $this->createMock(TemplateView::class);
        $this->subject->_set('view', $this->viewMock);

        $this->userRepositoryMock = $this->getMockBuilder(FrontendUserRepository::class)
            ->disableOriginalConstructor()->getMock();
        $this->subject->injectFrontendUserRepository($this->userRepositoryMock);

        $this->userGroupRepositoryMock = $this->getMockBuilder(FrontendUserGroupRepository::class)
            ->disableOriginalConstructor()->getMock();
        $this->subject->injectFrontendUserGroupRepository($this->userGroupRepositoryMock);

        $this->credentialsGeneratorMock = $this->createMock(CredentialsGenerator::class);
        $this->subject->injectCredentialsGenerator($this->credentialsGeneratorMock);

        $this->userValidatorMock = $this->createMock(UserValidator::class);
        $this->subject->injectUserValidator($this->userValidatorMock);
        $this->captchaFactoryMock = $this->createMock(CaptchaFactory::class);
        $this->subject->injectCaptchaFactory($this->captchaFactoryMock);
        $this->captchaValidatorMock = $this->createMock(CaptchaValidator::class);
        $this->subject->injectCaptchaValidator($this->captchaValidatorMock);

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

        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', $user],
            ['selectedUserGroup', self::anything()]
        );

        $this->subject->newAction($user);
    }

    /**
     * @test
     */
    public function newActionWithoutUserPassesVirginUserToView(): void
    {
        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', self::isInstanceOf(FrontendUser::class)],
            ['selectedUserGroup', self::anything()]
        );

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionWithNullUserPassesVirginUserToView(): void
    {
        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', self::isInstanceOf(FrontendUser::class)],
            ['selectedUserGroup', self::anything()]
        );

        $this->subject->newAction(null);
    }

    /**
     * @test
     */
    public function newActionWithUserGroupPassesProvidedUserGroupUidToView(): void
    {
        $userGroupUid = 5;
        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', $userGroupUid]
        );

        $this->subject->newAction(null, $userGroupUid);
    }

    /**
     * @test
     */
    public function newActionWithNullUserGroupPassesNullUserGroupToView(): void
    {
        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', null]
        );

        $this->subject->newAction(null, null);
    }

    /**
     * @test
     */
    public function newActionWithMissingUserGroupPassesNullUserGroupToView(): void
    {
        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', null]
        );

        $this->subject->newAction(null, null);
    }

    /**
     * @test
     */
    public function newActionWithoutUserCanPassVirginSubclassedUserToView(): void
    {
        // @phpstan-ignore-next-line We know that the necessary array keys exist.
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FrontendUser::class] = ['className' => XclassFrontendUser::class];

        $this->viewMock->expects(self::atLeast(2))->method('assign')->withConsecutive(
            ['user', self::isInstanceOf(XclassFrontendUser::class)],
            ['selectedUserGroup', self::anything()]
        );

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
        $userGroups = $this->createMock(QueryResultInterface::class);
        $this->userGroupRepositoryMock->method('findByUids')->with([$groupUid1, $groupUid2])->willReturn($userGroups);

        $this->viewMock->expects(self::atLeast(3))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', self::anything()],
            ['userGroups', $userGroups]
        );

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionWithPassesNewCaptchaToView(): void
    {
        $this->subject->_set('settings', ['captcha' => '1']);
        $captcha = new Captcha();
        $this->captchaFactoryMock->method('generateChallenge')->willReturn($captcha);

        $this->viewMock->expects(self::atLeast(3))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', self::anything()],
            ['captcha', $captcha]
        );

        $this->subject->newAction(null);
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

        $this->viewMock->expects(self::exactly(2))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', self::anything()]
        );

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

        $this->viewMock->expects(self::exactly(2))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', self::anything()]
        );

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionWithRedirectUrlInSiteInGetPassesRedirectUrlToView(): void
    {
        $redirectUrl = 'https://example.com/';
        $_GET['redirect_url'] = $redirectUrl;

        $this->viewMock->expects(self::exactly(3))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', self::anything()],
            ['redirectUrl', $redirectUrl]
        );

        $this->subject->newAction();
    }

    /**
     * @test
     */
    public function newActionWithRedirectUrlInSiteInPostPassesRedirectUrlToView(): void
    {
        $redirectUrl = 'https://example.com/';
        $_POST['redirect_url'] = $redirectUrl;

        $this->viewMock->expects(self::exactly(3))->method('assign')->withConsecutive(
            ['user', self::anything()],
            ['selectedUserGroup', self::anything()],
            ['redirectUrl', $redirectUrl]
        );

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
        $this->userValidatorMock->expects(self::once())->method('setSettings')->with($settings);

        $this->subject->initializeCreateAction();

        self::assertSame($this->userValidatorMock, $userArgument->getValidator());
    }

    /**
     * @test
     */
    public function initializeCreateActionWithoutUserArgumentNotTouchesUserValidator(): void
    {
        $this->subject->_set('settings', []);

        $this->userValidatorMock->expects(self::never())->method('setSettings');

        $this->subject->initializeCreateAction();
    }

    /**
     * @test
     */
    public function initializeCreateActionWithCaptchaArgumentSetsCaptchaValidatorWithSettings(): void
    {
        $captcha = new Captcha();
        $captchaArgument = new ExtbaseArgument('captcha', Captcha::class);
        $captchaArgument->setValue($captcha);
        $this->controllerArguments->addArgument($captchaArgument);

        $settings = ['captcha' => '1'];
        $this->subject->_set('settings', $settings);
        $this->captchaValidatorMock->expects(self::once())->method('setSettings')->with($settings);

        $this->subject->initializeCreateAction();

        self::assertSame($this->captchaValidatorMock, $captchaArgument->getValidator());
    }

    /**
     * @test
     */
    public function initializeCreateActionWithoutCaptchaArgumentNotTouchesCaptchaValidator(): void
    {
        $this->subject->_set('settings', []);

        $this->captchaValidatorMock->expects(self::never())->method('setSettings');

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
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

        $this->subject->createAction($user);

        self::assertSame($systemFolderUid, $user->getPid());
    }

    /**
     * @test
     */
    public function createActionWithAllowedExistentGroupUidSetsGivenGroup(): void
    {
        $groupUid = 4;
        $group = new FrontendUserGroup();
        $this->subject->_set('settings', ['groupsForNewUsers' => (string)$groupUid]);
        $this->userGroupRepositoryMock->method('findByUid')->with($groupUid)->willReturn($group);
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

        $user = new FrontendUser();
        $this->subject->createAction($user, $groupUid);

        self::assertCount(1, $user->getUserGroup());
        self::assertContains($group, $user->getUserGroup());
    }

    /**
     * @test
     */
    public function createActionWithAllowedInexistentGroupUidSetsOtherGroupsFromConfiguration(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->userGroupRepositoryMock->method('findByUid')->with($groupUid1)->willReturn(null);
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');
        /** @var ObjectStorage<FrontendUserGroup> $userGroupsFromRepository */
        $userGroupsFromRepository = new ObjectStorage();
        $userGroup2 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup2);
        $this->userGroupRepositoryMock->expects(self::atLeastOnce())->method('findByUids')
            ->with([$groupUid1, $groupUid2])
            ->willReturn(new TestingQueryResult($userGroupsFromRepository));

        $user = new FrontendUser();
        $this->subject->createAction($user, $groupUid1);

        $userGroupsFromUser = $user->getUserGroup();
        self::assertCount(1, $user->getUserGroup());
        self::assertTrue($userGroupsFromUser->contains($userGroup2));
    }

    /**
     * @test
     */
    public function createActionWithNotAllowedGroupUidSetsAllGroupsFromConfiguration(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');
        /** @var ObjectStorage<FrontendUserGroup> $userGroupsFromRepository */
        $userGroupsFromRepository = new ObjectStorage();
        $userGroup1 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup1);
        $userGroup2 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup2);
        $this->userGroupRepositoryMock->expects(self::atLeastOnce())->method('findByUids')
            ->with([$groupUid1, $groupUid2])
            ->willReturn(new TestingQueryResult($userGroupsFromRepository));

        $user = new FrontendUser();
        $this->subject->createAction($user, 123);

        $userGroupsFromUser = $user->getUserGroup();
        self::assertCount(2, $userGroupsFromUser);
        self::assertTrue($userGroupsFromUser->contains($userGroup1));
        self::assertTrue($userGroupsFromUser->contains($userGroup2));
    }

    /**
     * @test
     */
    public function createActionWithNullGroupUidSetsAllGroupsFromConfiguration(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');
        /** @var ObjectStorage<FrontendUserGroup> $userGroupsFromRepository */
        $userGroupsFromRepository = new ObjectStorage();
        $userGroup1 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup1);
        $userGroup2 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup2);
        $this->userGroupRepositoryMock->expects(self::atLeastOnce())->method('findByUids')
            ->with([$groupUid1, $groupUid2])
            ->willReturn(new TestingQueryResult($userGroupsFromRepository));

        $user = new FrontendUser();
        $this->subject->createAction($user, null);

        $userGroupsFromUser = $user->getUserGroup();
        self::assertCount(2, $userGroupsFromUser);
        self::assertTrue($userGroupsFromUser->contains($userGroup1));
        self::assertTrue($userGroupsFromUser->contains($userGroup2));
    }

    /**
     * @test
     */
    public function createActionWithNullGroupUidAndNoConfiguredGroupsSetsNoGroups(): void
    {
        $this->subject->_set('settings', ['groupsForNewUsers' => '']);
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

        $user = new FrontendUser();
        $this->subject->createAction($user, null);

        $userGroupsFromUser = $user->getUserGroup();
        self::assertCount(0, $userGroupsFromUser);
    }

    /**
     * @test
     */
    public function createActionWithZeroGroupUidSetsAllGroupsFromConfiguration(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');
        /** @var ObjectStorage<FrontendUserGroup> $userGroupsFromRepository */
        $userGroupsFromRepository = new ObjectStorage();
        $userGroup1 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup1);
        $userGroup2 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup2);
        $this->userGroupRepositoryMock->expects(self::atLeastOnce())->method('findByUids')
            ->with([$groupUid1, $groupUid2])
            ->willReturn(new TestingQueryResult($userGroupsFromRepository));

        $user = new FrontendUser();
        $this->subject->createAction($user, 0);

        $userGroupsFromUser = $user->getUserGroup();
        self::assertCount(2, $userGroupsFromUser);
        self::assertTrue($userGroupsFromUser->contains($userGroup1));
        self::assertTrue($userGroupsFromUser->contains($userGroup2));
    }

    /**
     * @test
     */
    public function createActionWithNegativeGroupUidSetsAllGroupsFromConfiguration(): void
    {
        $groupUid1 = 4;
        $groupUid2 = 5;
        $this->subject->_set('settings', ['groupsForNewUsers' => $groupUid1 . ',' . $groupUid2]);
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');
        /** @var ObjectStorage<FrontendUserGroup> $userGroupsFromRepository */
        $userGroupsFromRepository = new ObjectStorage();
        $userGroup1 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup1);
        $userGroup2 = new FrontendUserGroup();
        $userGroupsFromRepository->attach($userGroup2);
        $this->userGroupRepositoryMock->expects(self::atLeastOnce())->method('findByUids')
            ->with([$groupUid1, $groupUid2])
            ->willReturn(new TestingQueryResult($userGroupsFromRepository));

        $user = new FrontendUser();
        $this->subject->createAction($user, -1);

        $userGroupsFromUser = $user->getUserGroup();
        self::assertCount(2, $userGroupsFromUser);
        self::assertTrue($userGroupsFromUser->contains($userGroup1));
        self::assertTrue($userGroupsFromUser->contains($userGroup2));
    }

    /**
     * @test
     */
    public function createActionGeneratesUsername(): void
    {
        $user = new FrontendUser();
        $this->credentialsGeneratorMock->expects(self::once())->method('generateUsernameForUser')->with($user);
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

        $this->subject->createAction($user);
    }

    /**
     * @test
     */
    public function createActionGeneratesPassword(): void
    {
        $user = new FrontendUser();
        $this->credentialsGeneratorMock->expects(self::once())->method('generatePasswordForUser')
            ->with($user)->willReturn('');

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
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

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
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

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
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

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
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

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
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

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
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

        $this->subject->createAction($user);

        self::assertSame($lastName, $user->getName());
    }

    /**
     * @test
     */
    public function createActionForUserWithNoNameAtAllKeepsEmptyFullyName(): void
    {
        $user = new FrontendUser();
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

        $this->subject->createAction($user);

        self::assertSame('', $user->getName());
    }

    /**
     * @test
     */
    public function createActionWithUserAddsProvidedUserToRepository(): void
    {
        $user = new FrontendUser();
        $this->userRepositoryMock->expects(self::once())->method('add')->with($user);
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

        $this->subject->createAction($user);
    }

    /**
     * @test
     */
    public function createActionWithUserPersistsEverything(): void
    {
        $user = new FrontendUser();
        $this->userRepositoryMock->expects(self::once())->method('persistAll');
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');

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
        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn(null);

        $this->subject->createAction($user);
    }

    /**
     * @test
     */
    public function createActionWithNullUserNotAddsAnythingToRepository(): void
    {
        $this->userRepositoryMock->expects(self::never())->method('add')->with(self::anything());

        $this->subject->createAction(null);
    }

    /**
     * @test
     */
    public function createActionWithoutUserNotAddsAnythingToRepository(): void
    {
        $this->userRepositoryMock->expects(self::never())->method('add')->with(self::anything());

        $this->subject->createAction();
    }

    /**
     * @test
     */
    public function createActionWithNullUserNotPersistsAnything(): void
    {
        $this->userRepositoryMock->expects(self::never())->method('persistAll');

        $this->subject->createAction(null);
    }

    /**
     * @test
     */
    public function createActionWithoutUserNotPersistsAnything(): void
    {
        $this->userRepositoryMock->expects(self::never())->method('persistAll');

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

        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');
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

        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');
        $this->subject->expects(self::once())->method('redirectToUri')->with($redirectUrl);

        $this->subject->createAction(new FrontendUser());
    }

    /**
     * @test
     */
    public function createActionWithUserWithExternalRedirectUrlNotRedirects(): void
    {
        $_POST['redirect_url'] = 'https://www.oliverklee.de/';

        $this->credentialsGeneratorMock->method('generatePasswordForUser')->with(self::anything())->willReturn('');
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
