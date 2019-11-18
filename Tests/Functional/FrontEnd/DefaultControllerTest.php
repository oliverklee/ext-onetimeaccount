<?php

declare(strict_types=1);

namespace OliverKlee\OneTimeAccount\Tests\Functional\FrontEnd;

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use OliverKlee\OneTimeAccount\Tests\Unit\FrontEnd\Fixtures\FakeDefaultController;
use PHPUnit\Framework\MockObject\MockObject;
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
     * @var FrontendUserAuthentication|MockObject
     */
    private $frontEndUser = null;

    protected function setUp()
    {
        parent::setUp();
        $this->setDummyServerVariables();

        $this->frontEndUser = $this->getMockBuilder(FrontendUserAuthentication::class)
            ->setMethods(['createUserSession', 'writeUC'])->getMock();
        /** @var TypoScriptFrontendController|MockObject $frontEndController */
        $frontEndController = $this->createMock(TypoScriptFrontendController::class);
        $frontEndController->fe_user = $this->frontEndUser;
        $GLOBALS['TSFE'] = $frontEndController;

        $this->subject = new FakeDefaultController();

        $configurationProxy = \Tx_Oelib_ConfigurationProxy::getInstance('onetimeaccount');
        $configurationProxy->setAsBoolean('enableConfigCheck', false);
        $configurationProxy->setAsBoolean('enableLogging', false);
    }

    /**
     * @return void
     */
    private function setDummyServerVariables()
    {
        $_SERVER['HTTP_HOST'] = 'www.example.com';
        $_SERVER['REQUEST_URI'] = '/index.php?id=42';
    }

    private function importFrontEndUsers()
    {
        $this->importDataSet(__DIR__ . '/../Fixtures/FrontEndUsers.xml');
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
        $this->importFrontEndUsers();

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
        $this->importFrontEndUsers();
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

        $this->importFrontEndUsers();
        $this->subject->setFormData(['username' => 'foo@example.com']);

        self::assertContains('index.php?id=42', $this->subject->loginUserAndCreateRedirectUrl());
    }

    /**
     * @test
     */
    public function loginUserAndCreateRedirectUrlWithForeignUrlReturnsCurrentUri()
    {
        $GLOBALS['_POST']['redirect_url'] = 'http://google.com/';

        $this->importFrontEndUsers();
        $this->subject->setFormData(['username' => 'foo@example.com']);

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

        $this->importFrontEndUsers();
        $this->subject->setFormData(['username' => 'foo@example.com']);

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

        $this->importFrontEndUsers();
        $this->subject->setFormData(['username' => 'foo@example.com']);

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

        $this->importFrontEndUsers();
        $this->subject->setFormData(['username' => 'foo@example.com']);

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

        $this->importFrontEndUsers();
        $this->subject->setFormData(['username' => 'foo@example.com']);
        $userData = $this->getDatabaseConnection()->selectSingleRow('*', 'fe_users', 'uid = 1');

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
        $this->importFrontEndUsers();
        $userGroupUid = 1;

        $this->subject->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid
        );

        self::assertSame(
            [['caption' => 'foo', 'value' => $userGroupUid]],
            $this->subject->listUserGroups()
        );
    }

    /**
     * @test
     */
    public function listUserGroupsForStringConfiguredAsUserGroupReturnsEmptyArray()
    {
        $this->importFrontEndUsers();
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
        $this->importFrontEndUsers();
        $userGroupUid = 1;

        $this->subject->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid
        );

        self::assertSame(
            [['caption' => 'foo', 'value' => $userGroupUid]],
            $this->subject->listUserGroups()
        );
    }

    /**
     * @test
     */
    public function listUserGroupsForTwoExistingButAndConfiguredUserGroupsReturnsBothConfiguredGroup()
    {
        $this->importFrontEndUsers();
        $userGroupUid1 = 1;
        $userGroupUid2 = 2;

        $this->subject->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid1 . ', ' . $userGroupUid2
        );

        self::assertSame(
            [
                ['caption' => 'foo', 'value' => $userGroupUid1],
                ['caption' => 'bar', 'value' => $userGroupUid2],
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
        $this->importFrontEndUsers();
        $this->subject->setConfigurationValue('groupForNewFeUsers', 3);
        $fieldsToHide = [];

        $this->subject->setUserGroupSubpartVisibility($fieldsToHide);

        self::assertContains('usergroup', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setUserGroupSubpartVisibilityForOneAvailableUserGroupAddsUserGroupSubpartToHideFields()
    {
        $this->importFrontEndUsers();
        $this->subject->setConfigurationValue('groupForNewFeUsers', 1);

        $fieldsToHide = [];
        $this->subject->setUserGroupSubpartVisibility($fieldsToHide);

        self::assertContains('usergroup', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setUserGroupSubpartVisibilityForTwoAvailableUserGroupDoesNotAddUserGroupSubpartToHideFields()
    {
        $this->importFrontEndUsers();
        $this->subject->setConfigurationValue('groupForNewFeUsers', '1,2');

        $fieldsToHide = [];
        $this->subject->setUserGroupSubpartVisibility($fieldsToHide);

        self::assertNotContains('usergroup', $fieldsToHide);
    }
}
