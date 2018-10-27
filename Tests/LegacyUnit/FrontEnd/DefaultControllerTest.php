<?php

namespace OliverKlee\Onetimeaccount\Tests\LegacyUnit\FrontEnd;

use OliverKlee\Onetimeaccount\Tests\LegacyUnit\FrontEnd\Fixtures\FakeDefaultController;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DefaultControllerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var bool
     */
    protected $backupGlobals = false;

    /**
     * @var FakeDefaultController
     */
    private $fixture = null;

    /**
     * @var \Tx_Oelib_TestingFramework
     */
    private $testingFramework = null;

    /**
     * @var FrontendUserAuthentication|\PHPUnit_Framework_MockObject_MockObject
     */
    private $frontEndUser = null;

    /**
     * @var array
     */
    private $serverBackup = [];

    protected function setUp()
    {
        $this->serverBackup = $_SERVER;
        $this->setDummyServerVariables();

        \Tx_Oelib_ConfigurationProxy::getInstance('onetimeaccount')->setAsBoolean('enableConfigCheck', false);

        $this->testingFramework = new \Tx_Oelib_TestingFramework('tx_seminars');

        $GLOBALS['TSFE'] = $this->getMock(TypoScriptFrontendController::class, [], [], '', false);
        $this->frontEndUser = $this->getMock(
            FrontendUserAuthentication::class,
            ['getAuthInfoArray', 'fetchUserRecord', 'createUserSession']
        );
        $GLOBALS['TSFE']->fe_user = $this->frontEndUser;

        $this->fixture = new FakeDefaultController();

        $configurationProxy = \Tx_Oelib_ConfigurationProxy::getInstance('onetimeaccount');
        $configurationProxy->setAsBoolean('enableConfigCheck', false);
        $configurationProxy->setAsBoolean('enableLogging', false);
    }

    protected function tearDown()
    {
        $this->testingFramework->cleanUp();

        $GLOBALS['TSFE'] = null;

        $_SERVER = $this->serverBackup;
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
     * Tests concerning getFormData
     */

    /**
     * @test
     */
    public function getFormDataReturnsNonEmptyDataSetViaSetFormData()
    {
        $this->fixture->setFormData(['foo' => 'bar']);

        self::assertSame(
            'bar',
            $this->fixture->getFormData('foo')
        );
    }

    /*
     * Tests concerning createInitialUserName
     */

    /**
     * @test
     */
    public function createInitialUserNameForEmailSourceAndForNonEmptyEmailReturnsTheEmail()
    {
        $this->fixture->setConfigurationValue('userNameSource', 'email');
        $this->fixture->setFormData(['email' => 'foo@example.com']);

        self::assertSame(
            'foo@example.com',
            $this->fixture->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForInvalidSourceAndForNonEmptyEmailReturnsTheEmail()
    {
        $this->fixture->setConfigurationValue('userNameSource', 'somethingInvalid');
        $this->fixture->setFormData(['email' => 'foo@example.com']);

        self::assertSame(
            'foo@example.com',
            $this->fixture->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForEmailSourceAndForEmptyEmailReturnsUser()
    {
        $this->fixture->setConfigurationValue('userNameSource', 'email');
        $this->fixture->setFormData(['email' => '']);

        self::assertSame(
            'user',
            $this->fixture->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForEmptyNameFieldsReturnsUser()
    {
        $this->fixture->setConfigurationValue('userNameSource', 'name');
        $this->fixture->setFormData([]);

        self::assertSame(
            'user',
            $this->fixture->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForNonEmptyFullNameFieldsReturnsLowercasedFullNameWithDots()
    {
        $this->fixture->setConfigurationValue('userNameSource', 'name');
        $this->fixture->setFormData(['name' => 'John Doe']);

        self::assertSame(
            'john.doe',
            $this->fixture->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForNonEmptyFullNameFieldsTrimsName()
    {
        $this->fixture->setConfigurationValue('userNameSource', 'name');
        $this->fixture->setFormData(['name' => ' John Doe ']);

        self::assertSame(
            'john.doe',
            $this->fixture->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForNonEmptyFirstAndLastReturnsFirstAndLastName()
    {
        $this->fixture->setConfigurationValue('userNameSource', 'name');
        $this->fixture->setFormData(['first_name' => 'John', 'last_name' => 'Doe']);

        self::assertSame(
            'john.doe',
            $this->fixture->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForNonEmptyFirstAndEmptyLastReturnsFirstName()
    {
        $this->fixture->setConfigurationValue('userNameSource', 'name');
        $this->fixture->setFormData(['first_name' => 'John', 'last_name' => '']);

        self::assertSame(
            'john',
            $this->fixture->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForEmptyFirstAndNonEmptyLastReturnsLastName()
    {
        $this->fixture->setConfigurationValue('userNameSource', 'name');
        $this->fixture->setFormData(['first_name' => '', 'last_name' => 'Doe']);

        self::assertSame(
            'doe',
            $this->fixture->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForTwoPartFirstNameReturnsBothParts()
    {
        $this->fixture->setConfigurationValue('userNameSource', 'name');
        $this->fixture->setFormData(['first_name' => 'John Sullivan', 'last_name' => 'Doe']);

        self::assertSame(
            'john.sullivan.doe',
            $this->fixture->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceDropsAmpersandAndComma()
    {
        $this->fixture->setConfigurationValue('userNameSource', 'name');
        $this->fixture->setFormData(['first_name' => 'Tom & Jerry', 'last_name' => 'Smith, Miller']);

        self::assertSame(
            'tom.jerry.smith.miller',
            $this->fixture->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceDropsSpecialCharacters()
    {
        $this->fixture->setConfigurationValue('userNameSource', 'name');
        $this->fixture->setFormData(['first_name' => 'Sölüläß', 'last_name' => 'Smith']);

        self::assertSame(
            'sll.smith',
            $this->fixture->createInitialUserName()
        );
    }

    /*
     * Tests concerning getUserName
     */

    /**
     * @test
     */
    public function getUserNameWithNonEmptyEmailReturnsNonEmptyString()
    {
        $this->fixture->setFormData(['email' => 'foo@example.com']);

        self::assertNotSame(
            '',
            $this->fixture->getUserName()
        );
    }

    /**
     * @test
     */
    public function getUserNameWithNonEmptyEmailReturnsStringStartingWithEmail()
    {
        $this->fixture->setFormData(['email' => 'foo@example.com']);

        self::assertRegExp(
            '/^foo@example\\.com/',
            $this->fixture->getUserName()
        );
    }

    /**
     * @test
     */
    public function getUserNameForNameSourceWithNonEmptyEmailReturnsStringStartingWithEmail()
    {
        $this->fixture->setConfigurationValue('userNameSource', 'name');
        $this->fixture->setFormData(['name' => 'John Doe']);

        self::assertRegExp(
            '/^john.doe/',
            $this->fixture->getUserName()
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
        $this->fixture->setFormData(['email' => 'foo@example.com']);

        self::assertNotSame(
            'foo@example.com',
            $this->fixture->getUserName()
        );
    }

    /**
     * @test
     */
    public function getUserNameWithEmptyEmailReturnsNonEmptyString()
    {
        $this->fixture->setFormData(['email' => '']);

        self::assertNotSame(
            '',
            $this->fixture->getUserName()
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
        $this->fixture->setFormData(['email' => '']);

        self::assertNotSame(
            'user',
            $this->fixture->getUserName()
        );
    }

    /*
     * Tests concerning getPassword
     */

    /**
     * @test
     */
    public function getPasswordReturnsPasswordWithEightCharacters()
    {
        self::assertSame(
            8,
            strlen($this->fixture->getPassword())
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
        $url = GeneralUtility::getIndpEnv('TYPO3_SITE_URL') . 'index.php?id=42';
        $GLOBALS['_POST']['redirect_url'] = $url;

        self::assertContains('index.php?id=42', $this->fixture->loginUserAndCreateRedirectUrl());
    }

    /**
     * @test
     */
    public function loginUserAndCreateRedirectUrlWithForeignUrlReturnsCurrentUri()
    {
        $GLOBALS['_POST']['redirect_url'] = 'http://google.com/';

        self::assertSame(
            GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL'),
            $this->fixture->loginUserAndCreateRedirectUrl()
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
            $this->fixture->loginUserAndCreateRedirectUrl()
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
            $this->fixture->loginUserAndCreateRedirectUrl()
        );
    }

    /**
     * @test
     */
    public function loginUserAndCreateRedirectUrlDisablesFrontEndUserPidCheck()
    {
        $GLOBALS['TSFE']->fe_user->checkPid = true;

        $this->fixture->loginUserAndCreateRedirectUrl();

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
        $this->fixture->setFormData(['username' => $userName]);

        $authenticationData = ['some authentication data'];
        $this->frontEndUser->expects(self::once())
            ->method('getAuthInfoArray')
            ->will(self::returnValue(['db_user' => $authenticationData]));

        $userData = ['uid' => 42, 'username' => $userName, 'password' => 'secret'];
        $this->frontEndUser->expects(self::once())
            ->method('fetchUserRecord')
            ->with($authenticationData, $userName)
            ->will(self::returnValue($userData));
        $this->frontEndUser->expects(self::once())->method('createUserSession')->with($userData);

        $this->fixture->loginUserAndCreateRedirectUrl();
    }

    /*
     * Tests concerning validateStringField
     */

    /**
     * @test
     */
    public function validateStringFieldForNotRequiredFieldReturnsTrue()
    {
        $this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertTrue(
            $this->fixture->validateStringField(
                ['elementName' => 'address']
            )
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function validateStringFieldForMissingFieldNameThrowsException()
    {
        $this->fixture->validateStringField([]);
    }

    /**
     * @test
     */
    public function validateStringFieldForNonEmptyRequiredFieldReturnsTrue()
    {
        $this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertTrue(
            $this->fixture->validateStringField(
                ['elementName' => 'name', 'value' => 'foo']
            )
        );
    }

    /**
     * @test
     */
    public function validateStringFieldForEmptyRequiredFieldReturnsFalse()
    {
        $this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertFalse(
            $this->fixture->validateStringField(
                ['elementName' => 'name', 'value' => '']
            )
        );
    }

    /*
     * Tests concerning validateIntegerField
     */

    /**
     * @test
     */
    public function validateIntegerFieldForRequiredFieldValueZeroReturnsFalse()
    {
        $this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertFalse(
            $this->fixture->validateIntegerField(
                ['elementName' => 'name', 'value' => 0]
            )
        );
    }

    /**
     * @test
     */
    public function validateIntegerFieldForNonRequiredFieldReturnsTrue()
    {
        $this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertTrue(
            $this->fixture->validateIntegerField(
                ['elementName' => 'address']
            )
        );
    }

    /**
     * @test
     */
    public function validateIntegerFieldForRequiredFieldValueNonZeroReturnsTrue()
    {
        $this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertTrue(
            $this->fixture->validateIntegerField(
                ['elementName' => 'name', 'value' => 1]
            )
        );
    }

    /**
     * @test
     */
    public function validateIntegerFieldForRequiredFieldValueStringReturnsFalse()
    {
        $this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertFalse(
            $this->fixture->validateIntegerField(
                ['elementName' => 'name', 'value' => 'foo']
            )
        );
    }

    /**
     * @test
     *
     * @expectedException \InvalidArgumentException
     */
    public function validateIntegerFieldForMissingFieldNameThrowsException()
    {
        $this->fixture->validateIntegerField([]);
    }

    /*
     * Tests concerning getPidForNewUserRecords
     */

    /**
     * @test
     */
    public function getPidForNewUserRecordsForEmptyConfigValueReturnsZero()
    {
        $this->fixture->setConfigurationValue(
            'systemFolderForNewFeUserRecords',
            ''
        );

        self::assertSame(
            0,
            $this->fixture->getPidForNewUserRecords()
        );
    }

    /**
     * @test
     */
    public function getPidForNewUserRecordsForConfigValueStringReturnsZero()
    {
        $this->fixture->setConfigurationValue(
            'systemFolderForNewFeUserRecords',
            'foo'
        );

        self::assertSame(
            0,
            $this->fixture->getPidForNewUserRecords()
        );
    }

    /**
     * @test
     */
    public function getPidForNewUserRecordsForConfigValueIntegerReturnsInteger()
    {
        $this->fixture->setConfigurationValue(
            'systemFolderForNewFeUserRecords',
            42
        );

        self::assertSame(
            42,
            $this->fixture->getPidForNewUserRecords()
        );
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

        $this->fixture->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid
        );

        self::assertSame(
            [['caption' => 'foo', 'value' => (string)$userGroupUid]],
            $this->fixture->listUserGroups()
        );
    }

    /**
     * @test
     */
    public function listUserGroupsForStringConfiguredAsUserGroupReturnsEmptyArray()
    {
        $this->fixture->setConfigurationValue('groupForNewFeUsers', 'foo');

        self::assertSame(
            [],
            $this->fixture->listUserGroups()
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

        $this->fixture->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid
        );

        self::assertSame(
            [['caption' => 'foo', 'value' => (string)$userGroupUid]],
            $this->fixture->listUserGroups()
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

        $this->fixture->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid1 . ', ' . $userGroupUid2
        );

        self::assertSame(
            [
                ['caption' => 'foo', 'value' => (string)$userGroupUid1],
                ['caption' => 'bar', 'value' => (string)$userGroupUid2],
            ],
            $this->fixture->listUserGroups()
        );
    }

    /*
     * Tests concerning listUserGroups
     */

    /**
     * @test
     */
    public function listUserGroupsForGroupForNewUsersEmptyReturnsEmptyArray()
    {
        $this->fixture->setConfigurationValue('groupForNewFeUsers', '');

        self::assertSame(
            [],
            $this->fixture->listUserGroups()
        );
    }

    /*
     * Tests concerning setAllNamesSubpartVisibility
     */

    /**
     * @test
     */
    public function setAllNamesSubpartVisibilityForAllNameRelatedFieldsHiddenAddsAllNamesSubpartToHideFields()
    {
        $fieldsToHide = ['name', 'gender', 'first_name', 'last_name'];
        $this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

        self::assertContains('all_names', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setAllNamesSubpartVisibilityForVisibleNameFieldDoesNotAddAllNamesSubpartToHideFields()
    {
        $fieldsToHide = ['gender', 'first_name', 'last_name'];
        $this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

        self::assertNotContains('all_names', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setAllNamesSubpartVisibilityForVisibleFirstNameFieldDoesNotAddAllNamesSubpartToHideFields()
    {
        $fieldsToHide = ['name', 'gender', 'last_name'];
        $this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

        self::assertNotContains('all_names', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setAllNamesSubpartVisibilityForVisibleLastNameFieldDoesNotAddAllNamesSubpartToHideFields()
    {
        $fieldsToHide = ['name', 'gender', 'first_name'];
        $this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

        self::assertNotContains('all_names', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setAllNamesSubpartVisibilityForVisibleGenderFieldDoesNotAddAllNamesSubpartToHideFields()
    {
        $fieldsToHide = ['name', 'first_name', 'last_name'];
        $this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

        self::assertNotContains('all_names', $fieldsToHide);
    }

    /*
     * Tests concerning setZipSubpartVisibility
     */

    /**
     * @test
     */
    public function setZipSubpartVisibilityForHiddenCityAndZipAddsZipOnlySubpartToHideFields()
    {
        $fieldsToHide = ['zip', 'city'];
        $this->fixture->setZipSubpartVisibility($fieldsToHide);

        self::assertContains('zip_only', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setZipSubpartVisibilityForShownCityAndZipAddsZipOnlySubpartToHideFields()
    {
        $fieldsToHide = [];
        $this->fixture->setZipSubpartVisibility($fieldsToHide);

        self::assertContains('zip_only', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setZipSubpartVisibilityForShownCityAndHiddenZipAddsZipOnlySubpartToHideFields()
    {
        $fieldsToHide = ['zip'];
        $this->fixture->setZipSubpartVisibility($fieldsToHide);

        self::assertContains('zip_only', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setZipSubpartVisibilityForHiddenCityAndShownZipDoesNotAddZipOnlySubpartToHideFields()
    {
        $fieldsToHide = ['city'];
        $this->fixture->setZipSubpartVisibility($fieldsToHide);

        self::assertNotContains('zip_only', $fieldsToHide);
    }

    /*
     * Tests concerning setUserGroupSubpartVisibility
     */

    /**
     * @test
     */
    public function setUserGroupSubpartVisibilityForNonExistingUserGroupAddsUserGroupSubpartToHideFields()
    {
        $this->fixture->setConfigurationValue(
            'groupForNewFeUsers',
            $this->testingFramework->getAutoIncrement('fe_groups')
        );
        $fieldsToHide = [];

        $this->fixture->setUserGroupSubpartVisibility($fieldsToHide);

        self::assertContains('usergroup', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setUserGroupSubpartVisibilityForOneAvailableUserGroupAddsUserGroupSubpartToHideFields()
    {
        $this->fixture->setConfigurationValue(
            'groupForNewFeUsers',
            $this->testingFramework->createFrontEndUserGroup()
        );

        $fieldsToHide = [];
        $this->fixture->setUserGroupSubpartVisibility($fieldsToHide);

        self::assertContains('usergroup', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setUserGroupSubpartVisibilityForTwoAvailableUserGroupDoesNotAddUserGroupSubpartToHideFields()
    {
        $this->fixture->setConfigurationValue(
            'groupForNewFeUsers',
            $this->testingFramework->createFrontEndUserGroup() . ',' .
            $this->testingFramework->createFrontEndUserGroup()
        );

        $fieldsToHide = [];
        $this->fixture->setUserGroupSubpartVisibility($fieldsToHide);

        self::assertNotContains('usergroup', $fieldsToHide);
    }

    /*
     * Tests concerning preprocessFormData
     */

    /**
     * @test
     */
    public function preprocessFormDataForNameHiddenUsesFirstNameAndLastNameAsName()
    {
        $this->fixture->setConfigurationValue(
            'feUserFieldsToDisplay',
            'first_name, last_name'
        );
        $this->fixture->setFormFieldsToShow();

        $formData = $this->fixture->preprocessFormData([
            'first_name' => 'foo',
            'last_name' => 'bar',
        ]);

        self::assertSame(
            'foo bar',
            $formData['name']
        );
    }

    /**
     * @test
     */
    public function preprocessFormDataForShownNameFieldUsesValueOfNameField()
    {
        $this->fixture->setConfigurationValue(
            'feUserFieldsToDisplay',
            'name,first_name,last_name'
        );
        $this->fixture->setFormFieldsToShow();

        $formData = $this->fixture->preprocessFormData([
            'name' => 'foobar',
            'first_name' => 'foo',
            'last_name' => 'bar',
        ]);

        self::assertSame(
            'foobar',
            $formData['name']
        );
    }

    /**
     * @test
     */
    public function preprocessFormDataForUserGroupSetInConfigurationSetsTheUsersGroupInFormData()
    {
        $userGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $this->fixture->setConfigurationValue(
            'feUserFieldsToDisplay',
            'name'
        );
        $this->fixture->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid
        );
        $this->fixture->setFormFieldsToShow();

        $formData = $this->fixture->preprocessFormData(['name' => 'bar']);

        self::assertSame(
            (string)$userGroupUid,
            $formData['usergroup']
        );
    }

    /**
     * @test
     */
    public function preprocessFormDataForTwoUserGroupsSetInConfigurationAndOneSelectedInFormSetsTheSelectedUserGroupInFormData(
    ) {
        $userGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $userGroupUid2 = $this->testingFramework->createFrontEndUserGroup();
        $this->fixture->setConfigurationValue(
            'feUserFieldsToDisplay',
            'name,usergroups'
        );
        $this->fixture->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid . ',' . $userGroupUid2
        );
        $this->fixture->setFormFieldsToShow();

        $formData = $this->fixture->preprocessFormData(
            ['usergroup' => $userGroupUid]
        );

        self::assertSame(
            $userGroupUid,
            $formData['usergroup']
        );
    }

    /**
     * @test
     */
    public function preprocessFormDataForTwoUserGroupsSetInConfigurationTheGroupFieldHiddenSetsTheUserGroupsFromConfiguration(
    ) {
        $userGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $userGroupUid2 = $this->testingFramework->createFrontEndUserGroup();
        $this->fixture->setConfigurationValue(
            'feUserFieldsToDisplay',
            'name'
        );
        $this->fixture->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid . ',' . $userGroupUid2
        );
        $this->fixture->setFormFieldsToShow();

        $formData = $this->fixture->preprocessFormData([]);

        self::assertSame(
            $userGroupUid . ',' . $userGroupUid2,
            $formData['usergroup']
        );
    }

    /**
     * @test
     */
    public function preprocessFormDataForUserGroupSetInConfigurationButNoGroupChosenInFormSetsTheUsersGroupFromConfiguration(
    ) {
        $userGroupUid = $this->testingFramework->createFrontEndUserGroup();
        $this->fixture->setConfigurationValue(
            'feUserFieldsToDisplay',
            'name,usergroup'
        );
        $this->fixture->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid
        );
        $this->fixture->setFormFieldsToShow();

        $formData = $this->fixture->preprocessFormData(
            ['name' => 'bar', 'usergroup' => '']
        );

        self::assertSame(
            (string)$userGroupUid,
            $formData['usergroup']
        );
    }
}
