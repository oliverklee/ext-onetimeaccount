<?php

namespace OliverKlee\OneTimeAccount\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\OneTimeAccount\Tests\Unit\FrontEnd\Fixtures\FakeDefaultController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DefaultControllerTest extends FunctionalTestCase
{
    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = ['typo3conf/ext/oelib', 'typo3conf/ext/onetimeaccount'];

    /**
     * @var FakeDefaultController
     */
    private $subject = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var FrontendUserAuthentication|\PHPUnit_Framework_MockObject_MockObject
     */
    private $frontEndUser = null;

    protected function setUp()
    {
        parent::setUp();
        $this->setDummyServerVariables();

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_onetimeaccount');

        $this->frontEndUser = $this->getMockBuilder(FrontendUserAuthentication::class)
            ->setMethods(['getAuthInfoArray', 'fetchUserRecord', 'createUserSession'])->getMock();
        $GLOBALS['TSFE'] = $this->createMock(TypoScriptFrontendController::class);
        $GLOBALS['TSFE']->fe_user = $this->frontEndUser;

        $this->subject = new FakeDefaultController();

        $configurationProxy = \Tx_Oelib_ConfigurationProxy::getInstance('onetimeaccount');
        $configurationProxy->setAsBoolean('enableConfigCheck', false);
        $configurationProxy->setAsBoolean('enableLogging', false);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUpWithoutDatabase();
        parent::tearDown();
    }

    /**
     * @return void
     */
    private function setDummyServerVariables()
    {
        $_SERVER['HTTP_HOST'] = 'www.example.com';
        $_SERVER['REQUEST_URI'] = '/index.php?id=42';
    }

    /*
     * Tests concerning getUserName
     */

    /**
     * @test
     */
    public function getUserNameWithNonEmptyEmailReturnsNonEmptyString()
    {
        $this->subject->setFormData(['email' => 'foo@example.com']);

        self::assertNotSame(
            '',
            $this->subject->getUserName()
        );
    }

    /**
     * @test
     */
    public function getUserNameWithNonEmptyEmailReturnsStringStartingWithEmail()
    {
        $this->subject->setFormData(['email' => 'foo@example.com']);

        self::assertRegExp(
            '/^foo@example\\.com/',
            $this->subject->getUserName()
        );
    }

    /**
     * @test
     */
    public function getUserNameForNameSourceWithNonEmptyEmailReturnsStringStartingWithEmail()
    {
        $this->subject->setConfigurationValue('userNameSource', 'name');
        $this->subject->setFormData(['name' => 'John Doe']);

        self::assertRegExp(
            '/^john.doe/',
            $this->subject->getUserName()
        );
    }

    /**
     * @test
     */
    public function getUserNameWithEmailOfExistingUserNameReturnsDifferentName()
    {
        $this->testingFramework->createFrontEndUser(
            $this->testingFramework->createFrontEndUserGroup(),
            ['username' => 'foo@example.com']
        );
        $this->subject->setFormData(['email' => 'foo@example.com']);

        self::assertNotSame(
            'foo@example.com',
            $this->subject->getUserName()
        );
    }

    /**
     * @test
     */
    public function getUserNameWithEmptyEmailReturnsNonEmptyString()
    {
        $this->subject->setFormData(['email' => '']);

        self::assertNotSame(
            '',
            $this->subject->getUserName()
        );
    }

    /**
     * @test
     */
    public function getUserNameWithEmptyEmailAndDefaultUserNameAlreadyExistingReturnsNewUniqueUsernameString()
    {
        $this->testingFramework->createFrontEndUser(
            '',
            ['username' => 'user']
        );
        $this->subject->setFormData(['email' => '']);

        self::assertNotSame(
            'user',
            $this->subject->getUserName()
        );
    }

    /*
     * Tests concerning loginUserAndCreateRedirectUrl
     */

    /**
     * @test
     */
    public function loginUserAndCreateRedirectUrlWithLocalRedirectUrlReturnsRedirectUrl()
    {
        $GLOBALS['_POST']['redirect_url'] = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'index.php?id=42';

        self::assertContains('index.php?id=42', $this->subject->loginUserAndCreateRedirectUrl());
    }

    /**
     * @test
     */
    public function loginUserAndCreateRedirectUrlWithForeignUrlReturnsCurrentUri()
    {
        $GLOBALS['_POST']['redirect_url'] = 'http://google.com/';

        self::assertSame(
            GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
            $this->subject->loginUserAndCreateRedirectUrl()
        );
    }

    /**
     * @test
     */
    public function loginUserAndCreateRedirectUrlWithEmptyRedirectUrlReturnsCurrentUri()
    {
        $GLOBALS['_POST']['redirect_url'] = '';

        self::assertSame(
            GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
            $this->subject->loginUserAndCreateRedirectUrl()
        );
    }

    /**
     * @test
     */
    public function loginUserAndCreateRedirectUrlWithMissingRedirectUrlReturnsCurrentUri()
    {
        unset($GLOBALS['_POST']['redirect_url']);

        self::assertSame(
            GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
            $this->subject->loginUserAndCreateRedirectUrl()
        );
    }

    /**
     * @test
     */
    public function loginUserAndCreateRedirectUrlDisablesFrontEndUserPidCheck()
    {
        $GLOBALS['TSFE']->fe_user->checkPid = true;

        $this->subject->loginUserAndCreateRedirectUrl();

        self::assertFalse(
            $GLOBALS['TSFE']->fe_user->checkPid
        );
    }

    /**
     * @test
     */
    public function loginUserAndCreateRedirectUrlCreatesUserSessionWithProvidedUserName()
    {
        $userName = 'john.doe';
        $this->subject->setFormData(['username' => $userName]);

        $authenticationData = ['some authentication data'];
        $this->frontEndUser->expects(self::once())
            ->method('getAuthInfoArray')
            ->willReturn(['db_user' => $authenticationData]);

        $userData = ['uid' => 42, 'username' => $userName, 'password' => 'secret'];
        $this->frontEndUser->expects(self::once())
            ->method('fetchUserRecord')
            ->with($authenticationData, $userName)
            ->willReturn($userData);
        $this->frontEndUser->expects(self::once())->method('createUserSession')->with($userData);

        $this->subject->loginUserAndCreateRedirectUrl();
    }

    /*
     * Tests concerning listUserGroups
     */

    /**
     * @test
     */
    public function listUserGroupsForExistingAndConfiguredUserGroupReturnsGroupTitleAndUid()
    {
        $userGroupUid = $this->testingFramework->createFrontEndUserGroup(
            ['title' => 'foo']
        );

        $this->subject->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid
        );

        self::assertSame(
            [['caption' => 'foo', 'value' => (string)$userGroupUid]],
            $this->subject->listUserGroups()
        );
    }

    /**
     * @test
     */
    public function listUserGroupsForStringConfiguredAsUserGroupReturnsEmptyArray()
    {
        $this->subject->setConfigurationValue('groupForNewFeUsers', 'foo');

        self::assertSame(
            [],
            $this->subject->listUserGroups()
        );
    }

    /**
     * @test
     */
    public function listUserGroupsForTwoExistingButOnlyOneConfiguredUserGroupReturnsOnlyConfiguredGroup()
    {
        $userGroupUid = $this->testingFramework->createFrontEndUserGroup(
            ['title' => 'foo']
        );
        $this->testingFramework->createFrontEndUserGroup(
            ['title' => 'bar']
        );

        $this->subject->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid
        );

        self::assertSame(
            [['caption' => 'foo', 'value' => (string)$userGroupUid]],
            $this->subject->listUserGroups()
        );
    }

    /**
     * @test
     */
    public function listUserGroupsForTwoExistingButAndConfiguredUserGroupsReturnsBothConfiguredGroup()
    {
        $userGroupUid1 = $this->testingFramework->createFrontEndUserGroup(
            ['title' => 'foo', 'crdate' => 1]
        );
        $userGroupUid2 = $this->testingFramework->createFrontEndUserGroup(
            ['title' => 'bar', 'crdate' => 2]
        );

        $this->subject->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid1 . ', ' . $userGroupUid2
        );

        self::assertSame(
            [
                ['caption' => 'foo', 'value' => (string)$userGroupUid1],
                ['caption' => 'bar', 'value' => (string)$userGroupUid2],
            ],
            $this->subject->listUserGroups()
        );
    }

    /**
     * @test
     */
    public function listUserGroupsForGroupForNewUsersEmptyReturnsEmptyArray()
    {
        $this->subject->setConfigurationValue('groupForNewFeUsers', '');

        self::assertSame(
            [],
            $this->subject->listUserGroups()
        );
    }

    /*
     * Tests concerning setUserGroupSubpartVisibility
     */

    /**
     * @test
     */
    public function setUserGroupSubpartVisibilityForNonExistingUserGroupAddsUserGroupSubpartToHideFields()
    {
        $this->subject->setConfigurationValue(
            'groupForNewFeUsers',
            $this->testingFramework->getAutoIncrement('fe_groups')
        );
        $fieldsToHide = [];

        $this->subject->setUserGroupSubpartVisibility($fieldsToHide);

        self::assertContains('usergroup', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setUserGroupSubpartVisibilityForOneAvailableUserGroupAddsUserGroupSubpartToHideFields()
    {
        $this->subject->setConfigurationValue(
            'groupForNewFeUsers',
            $this->testingFramework->createFrontEndUserGroup()
        );

        $fieldsToHide = [];
        $this->subject->setUserGroupSubpartVisibility($fieldsToHide);

        self::assertContains('usergroup', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setUserGroupSubpartVisibilityForTwoAvailableUserGroupDoesNotAddUserGroupSubpartToHideFields()
    {
        $this->subject->setConfigurationValue(
            'groupForNewFeUsers',
            $this->testingFramework->createFrontEndUserGroup() . ',' .
            $this->testingFramework->createFrontEndUserGroup()
        );

        $fieldsToHide = [];
        $this->subject->setUserGroupSubpartVisibility($fieldsToHide);

        self::assertNotContains('usergroup', $fieldsToHide);
    }
}
