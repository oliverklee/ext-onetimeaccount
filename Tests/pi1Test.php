<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Test case.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_onetimeaccount_pi1Test extends tx_phpunit_testcase {
	/**
	 * @var tx_onetimeaccount_Tests_Fixtures_FakePi1
	 */
	private $fixture = NULL;

	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework = NULL;

	/**
	 * @var tslib_feUserAuth|PHPUnit_Framework_MockObject_MockObject
	 */
	private $frontEndUser = NULL;

	protected function setUp() {
		tx_oelib_configurationProxy::getInstance('onetimeaccount')->setAsBoolean('enableConfigCheck', FALSE);

		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');

		$GLOBALS['TSFE'] = $this->getMock('tslib_fe', array(), array(), '', FALSE);
		$this->frontEndUser = $this->getMock(
			'tslib_feUserAuth', array('getAuthInfoArray', 'fetchUserRecord', 'createUserSession')
		);
		$GLOBALS['TSFE']->fe_user = $this->frontEndUser;

		$this->fixture = new tx_onetimeaccount_Tests_Fixtures_FakePi1(
			array(
				'isStaticTemplateLoaded' => 1,
				'userNameSource' => 'email',
			)
		);

		$configurationProxy = tx_oelib_configurationProxy::getInstance('onetimeaccount');
		$configurationProxy->setAsBoolean('enableConfigCheck', FALSE);
		$configurationProxy->setAsBoolean('enableLogging', FALSE);
	}

	protected function tearDown() {
		$this->testingFramework->cleanUp();

		$GLOBALS['TSFE'] = NULL;
	}


	/*
	 * Tests concerning getFormData
	 */

	/**
	 * @test
	 */
	public function getFormDataReturnsNonEmptyDataSetViaSetFormData() {
		$this->fixture->setFormData(array('foo' => 'bar'));

		$this->assertSame(
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
	public function createInitialUserNameForEmailSourceAndForNonEmptyEmailReturnsTheEmail() {
		$this->fixture->setConfigurationValue('userNameSource', 'email');
		$this->fixture->setFormData(array('email' => 'foo@example.com'));

		$this->assertSame(
			'foo@example.com',
			$this->fixture->createInitialUserName()
		);
	}

	/**
	 * @test
	 */
	public function createInitialUserNameForInvaliedSourceAndForNonEmptyEmailReturnsTheEmail() {
		$this->fixture->setConfigurationValue('userNameSource', 'somethingInvalid');
		$this->fixture->setFormData(array('email' => 'foo@example.com'));

		$this->assertSame(
			'foo@example.com',
			$this->fixture->createInitialUserName()
		);
	}

	/**
	 * @test
	 */
	public function createInitialUserNameForEmailSourceAndForEmptyEmailReturnsUser() {
		$this->fixture->setConfigurationValue('userNameSource', 'email');
		$this->fixture->setFormData(array('email' => ''));

		$this->assertSame(
			'user',
			$this->fixture->createInitialUserName()
		);
	}

	/**
	 * @test
	 */
	public function createInitialUserNameForNameSourceAndForEmptyNameFieldsReturnsUser() {
		$this->fixture->setConfigurationValue('userNameSource', 'name');
		$this->fixture->setFormData(array());

		$this->assertSame(
			'user',
			$this->fixture->createInitialUserName()
		);
	}

	/**
	 * @test
	 */
	public function createInitialUserNameForNameSourceAndForNonEmptyFullNameFieldsReturnsLowercasedFullNameWithDots() {
		$this->fixture->setConfigurationValue('userNameSource', 'name');
		$this->fixture->setFormData(array('name' => 'John Doe'));

		$this->assertSame(
			'john.doe',
			$this->fixture->createInitialUserName()
		);
	}

	/**
	 * @test
	 */
	public function createInitialUserNameForNameSourceAndForNonEmptyFullNameFieldsTrimsName() {
		$this->fixture->setConfigurationValue('userNameSource', 'name');
		$this->fixture->setFormData(array('name' => ' John Doe '));

		$this->assertSame(
			'john.doe',
			$this->fixture->createInitialUserName()
		);
	}

	/**
	 * @test
	 */
	public function createInitialUserNameForNameSourceAndForNonEmptyFirstAndLastReturnsFirstAndLastName() {
		$this->fixture->setConfigurationValue('userNameSource', 'name');
		$this->fixture->setFormData(array('first_name' => 'John', 'last_name' => 'Doe'));

		$this->assertSame(
			'john.doe',
			$this->fixture->createInitialUserName()
		);
	}

	/**
	 * @test
	 */
	public function createInitialUserNameForNameSourceAndForNonEmptyFirstAndEmptyLastReturnsFirstName() {
		$this->fixture->setConfigurationValue('userNameSource', 'name');
		$this->fixture->setFormData(array('first_name' => 'John', 'last_name' => ''));

		$this->assertSame(
			'john',
			$this->fixture->createInitialUserName()
		);
	}

	/**
	 * @test
	 */
	public function createInitialUserNameForNameSourceAndForEmptyFirstAndNonEmptyLastReturnsLastName() {
		$this->fixture->setConfigurationValue('userNameSource', 'name');
		$this->fixture->setFormData(array('first_name' => '', 'last_name' => 'Doe'));

		$this->assertSame(
			'doe',
			$this->fixture->createInitialUserName()
		);
	}

	/**
	 * @test
	 */
	public function createInitialUserNameForNameSourceAndForTwoPartFirstNameReturnsBothParts() {
		$this->fixture->setConfigurationValue('userNameSource', 'name');
		$this->fixture->setFormData(array('first_name' => 'John Sullivan', 'last_name' => 'Doe'));

		$this->assertSame(
			'john.sullivan.doe',
			$this->fixture->createInitialUserName()
		);
	}

	/**
	 * @test
	 */
	public function createInitialUserNameForNameSourceDropsAmpersandAndComma() {
		$this->fixture->setConfigurationValue('userNameSource', 'name');
		$this->fixture->setFormData(array('first_name' => 'Tom & Jerry', 'last_name' => 'Smith, Miller'));

		$this->assertSame(
			'tom.jerry.smith.miller',
			$this->fixture->createInitialUserName()
		);
	}

	/**
	 * @test
	 */
	public function createInitialUserNameForNameSourceDropsSpecialCharacters() {
		$this->fixture->setConfigurationValue('userNameSource', 'name');
		$this->fixture->setFormData(array('first_name' => 'Sölüläß', 'last_name' => 'Smith'));

		$this->assertSame(
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
	public function getUserNameWithNonEmptyEmailReturnsNonEmptyString() {
		$this->fixture->setFormData(array('email' => 'foo@example.com'));

		$this->assertNotSame(
			'',
			$this->fixture->getUserName()
		);
	}

	/**
	 * @test
	 */
	public function getUserNameWithNonEmptyEmailReturnsStringStartingWithEmail() {
		$this->fixture->setFormData(array('email' => 'foo@example.com'));

		$this->assertRegExp(
			'/^foo@example\.com/',
			$this->fixture->getUserName()
		);
	}

	/**
	 * @test
	 */
	public function getUserNameForNameSourceWithNonEmptyEmailReturnsStringStartingWithEmail() {
		$this->fixture->setConfigurationValue('userNameSource', 'name');
		$this->fixture->setFormData(array('name' => 'John Doe'));

		$this->assertRegExp(
			'/^john.doe/',
			$this->fixture->getUserName()
		);
	}

	/**
	 * @test
	 */
	public function getUserNameWithEmailOfExistingUserNameReturnsDifferentName() {
		$this->testingFramework->createFrontEndUser(
			$this->testingFramework->createFrontEndUserGroup(),
			array('username' => 'foo@example.com')
		);
		$this->fixture->setFormData(array('email' => 'foo@example.com'));

		$this->assertNotSame(
			'foo@example.com',
			$this->fixture->getUserName()
		);
	}

	/**
	 * @test
	 */
	public function getUserNameWithEmptyEmailReturnsNonEmptyString() {
		$this->fixture->setFormData(array('email' => ''));

		$this->assertNotSame(
			'',
			$this->fixture->getUserName()
		);
	}

	/**
	 * @test
	 */
	public function getUserNameWithEmptyEmailAndDefaultUserNameAlreadyExistingReturnsNewUniqueUsernameString() {
		$this->testingFramework->createFrontEndUser(
			'', array('username' => 'user')
		);
		$this->fixture->setFormData(array('email' => ''));

		$this->assertNotSame(
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
	public function getPasswordReturnsPasswordWithEightCharacters() {
		$this->assertSame(
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
	public function loginUserAndCreateRedirectUrlWithLocalRedirectUrlReturnsRedirectUrl() {
		$url = t3lib_div::getIndpEnv('TYPO3_SITE_URL') . 'index.php?id=42';
		$GLOBALS['_POST']['redirect_url'] = $url;

		$this->assertSame(
			$url,
			$this->fixture->loginUserAndCreateRedirectUrl()
		);
	}

	/**
	 * @test
	 */
	public function loginUserAndCreateRedirectUrlWithForeignUrlReturnsCurrentUri() {
		$GLOBALS['_POST']['redirect_url'] = 'http://google.com/';

		$this->assertSame(
			t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'),
			$this->fixture->loginUserAndCreateRedirectUrl()
		);
	}

	/**
	 * @test
	 */
	public function loginUserAndCreateRedirectUrlWithEmptyRedirectUrlReturnsCurrentUri() {
		$GLOBALS['_POST']['redirect_url'] = '';

		$this->assertSame(
			t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'),
			$this->fixture->loginUserAndCreateRedirectUrl()
		);
	}

	/**
	 * @test
	 */
	public function loginUserAndCreateRedirectUrlWithMissingRedirectUrlReturnsCurrentUri() {
		unset($GLOBALS['_POST']['redirect_url']);

		$this->assertSame(
			t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'),
			$this->fixture->loginUserAndCreateRedirectUrl()
		);
	}

	/**
	 * @test
	 */
	public function loginUserAndCreateRedirectUrlDisablesFrontEndUserPidCheck() {
		$GLOBALS['TSFE']->fe_user->checkPid = TRUE;

		$this->fixture->loginUserAndCreateRedirectUrl();

		$this->assertFalse(
			$GLOBALS['TSFE']->fe_user->checkPid
		);
	}

	/**
	 * @test
	 */
	public function loginUserAndCreateRedirectUrlCreatesUserSessionWithProvidedUserName() {
		$userName = 'john.doe';
		$this->fixture->setFormData(array('username' => $userName));

		$authenticationData = array('some authentication data');
		$this->frontEndUser->expects($this->once())->method('getAuthInfoArray')->will($this->returnValue(array('db_user' => $authenticationData)));

		$userData = array('uid' => 42, 'username' => $userName, 'password' => 'secret');
		$this->frontEndUser->expects($this->once())->method('fetchUserRecord')->with($authenticationData, $userName)->will($this->returnValue($userData));
		$this->frontEndUser->expects($this->once())->method('createUserSession')->with($userData);

		$this->fixture->loginUserAndCreateRedirectUrl();
	}


	/*
	 * Tests concerning validateStringField
	 */

	/**
	 * @test
	 */
	public function validateStringFieldForNotRequiredFieldReturnsTrue() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertTrue(
			$this->fixture->validateStringField(
				array('elementName' => 'address')
			)
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function validateStringFieldForMissingFieldNameThrowsException() {
		$this->fixture->validateStringField(array());

	}

	/**
	 * @test
	 */
	public function validateStringFieldForNonEmptyRequiredFieldReturnsTrue() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertTrue(
			$this->fixture->validateStringField(
				array('elementName' => 'name', 'value' => 'foo')
			)
		);
	}

	/**
	 * @test
	 */
	public function validateStringFieldForEmptyRequiredFieldReturnsFalse() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertFalse(
			$this->fixture->validateStringField(
				array('elementName' => 'name', 'value' => '')
			)
		);
	}


	/*
	 * Tests concerning validateIntegerField
	 */

	/**
	 * @test
	 */
	public function validateIntegerFieldForRequiredFieldValueZeroReturnsFalse() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertFalse(
			$this->fixture->validateIntegerField(
				array('elementName' => 'name', 'value' => 0)
			)
		);
	}

	/**
	 * @test
	 */
	public function validateIntegerFieldForNonRequiredFieldReturnsTrue() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertTrue(
			$this->fixture->validateIntegerField(
				array('elementName' => 'address')
			)
		);
	}

	/**
	 * @test
	 */
	public function validateIntegerFieldForRequiredFieldValueNonZeroReturnsTrue() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertTrue(
			$this->fixture->validateIntegerField(
				array('elementName' => 'name', 'value' => 1)
			)
		);
	}

	/**
	 * @test
	 */
	public function validateIntegerFieldForRequiredFieldValueStringReturnsFalse() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertFalse(
			$this->fixture->validateIntegerField(
				array('elementName' => 'name', 'value' => 'foo')
			)
		);
	}

	/**
	 * @test
	 *
	 * @expectedException InvalidArgumentException
	 */
	public function validateIntegerFieldForMissingFieldNameThrowsException() {
		$this->fixture->validateIntegerField(array());
	}


	/*
	 * Tests concerning getPidForNewUserRecords
	 */

	/**
	 * @test
	 */
	public function getPidForNewUserRecordsForEmptyConfigValueReturnsZero() {
		$this->fixture->setConfigurationValue(
			'systemFolderForNewFeUserRecords', ''
		);

		$this->assertSame(
			0,
			$this->fixture->getPidForNewUserRecords()
		);
	}

	/**
	 * @test
	 */
	public function getPidForNewUserRecordsForConfigValueStringReturnsZero() {
		$this->fixture->setConfigurationValue(
			'systemFolderForNewFeUserRecords', 'foo'
		);

		$this->assertSame(
			0,
			$this->fixture->getPidForNewUserRecords()
		);
	}

	/**
	 * @test
	 */
	public function getPidForNewUserRecordsForConfigValueIntegerReturnsInteger() {
		$this->fixture->setConfigurationValue(
			'systemFolderForNewFeUserRecords', 42
		);

		$this->assertSame(
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
	public function listUserGroupsForExistingAndConfiguredUserGroupReturnsGroupTitleAndUid() {
		$userGroupUid = $this->testingFramework->createFrontEndUserGroup(
			array('title' => 'foo')
		);

		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers', $userGroupUid
		);

		$this->assertSame(
			array(array('caption' => 'foo', 'value' => (string) $userGroupUid)),
			$this->fixture->listUserGroups()
		);
	}

	/**
	 * @test
	 */
	public function listUserGroupsForStringConfiguredAsUserGroupReturnsEmptyArray() {
		$this->fixture->setConfigurationValue('groupForNewFeUsers', 'foo');

		$this->assertSame(
			array(),
			$this->fixture->listUserGroups()
		);
	}

	/**
	 * @test
	 */
	public function listUserGroupsForTwoExistingButOnlyOneConfiguredUserGroupReturnsOnlyConfiguredGroup() {
		$userGroupUid = $this->testingFramework->createFrontEndUserGroup(
			array('title' => 'foo')
		);
		$this->testingFramework->createFrontEndUserGroup(
			array('title' => 'bar')
		);

		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers', $userGroupUid
		);

		$this->assertSame(
			array(array('caption' => 'foo', 'value' => (string) $userGroupUid)),
			$this->fixture->listUserGroups()
		);
	}

	/**
	 * @test
	 */
	public function listUserGroupsForTwoExistingButAndConfiguredUserGroupsReturnsBothConfiguredGroup() {
		$userGroupUid1 = $this->testingFramework->createFrontEndUserGroup(
			array('title' => 'foo', 'crdate' => 1)
		);
		$userGroupUid2 = $this->testingFramework->createFrontEndUserGroup(
			array('title' => 'bar', 'crdate' => 2)
		);

		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers', $userGroupUid1 . ', ' . $userGroupUid2
		);

		$this->assertSame(
			array(
				array('caption' => 'foo', 'value' => (string) $userGroupUid1),
				array('caption' => 'bar', 'value' => (string) $userGroupUid2),
			),
			$this->fixture->listUserGroups()
		);
	}


	/*
	 * Tests concerning listUserGroups
	 */

	/**
	 * @test
	 */
	public function listUserGroupsForGroupForNewUsersEmptyReturnsEmptyArray() {
		$this->fixture->setConfigurationValue('groupForNewFeUsers', '');

		$this->assertSame(
			array(),
			$this->fixture->listUserGroups()
		);
	}


	/*
	 * Tests concerning setAllNamesSubpartVisibility
	 */

	/**
	 * @test
	 */
	public function setAllNamesSubpartVisibilityForAllNameRelatedFieldsHiddenAddsAllNamesSubpartToHideFields() {
		$fieldsToHide = array('name', 'gender', 'first_name', 'last_name');
		$this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

		$this->assertTrue(
			in_array('all_names', $fieldsToHide)
		);
	}

	/**
	 * @test
	 */
	public function setAllNamesSubpartVisibilityForVisibleNameFieldDoesNotAddAllNamesSubpartToHideFields() {
		$fieldsToHide = array('gender', 'first_name', 'last_name');
		$this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

		$this->assertFalse(
			in_array('all_names', $fieldsToHide)
		);
	}

	/**
	 * @test
	 */
	public function setAllNamesSubpartVisibilityForVisibleFirstNameFieldDoesNotAddAllNamesSubpartToHideFields() {
		$fieldsToHide = array('name', 'gender', 'last_name');
		$this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

		$this->assertFalse(
			in_array('all_names', $fieldsToHide)
		);
	}

	/**
	 * @test
	 */
	public function setAllNamesSubpartVisibilityForVisibleLastNameFieldDoesNotAddAllNamesSubpartToHideFields() {
		$fieldsToHide = array('name', 'gender', 'first_name');
		$this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

		$this->assertFalse(
			in_array('all_names', $fieldsToHide)
		);
	}

	/**
	 * @test
	 */
	public function setAllNamesSubpartVisibilityForVisibleGenderFieldDoesNotAddAllNamesSubpartToHideFields() {
		$fieldsToHide = array('name', 'first_name', 'last_name');
		$this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

		$this->assertFalse(
			in_array('all_names', $fieldsToHide)
		);
	}


	/*
	 * Tests concerning setZipSubpartVisibility
	 */

	/**
	 * @test
	 */
	public function setZipSubpartVisibilityForHiddenCityAndZipAddsZipOnlySubpartToHideFields() {
		$fieldsToHide = array('zip', 'city');
		$this->fixture->setZipSubpartVisibility($fieldsToHide);

		$this->assertTrue(
			in_array('zip_only', $fieldsToHide)
		);
	}

	/**
	 * @test
	 */
	public function setZipSubpartVisibilityForShownCityAndZipAddsZipOnlySubpartToHideFields() {
		$fieldsToHide = array();
		$this->fixture->setZipSubpartVisibility($fieldsToHide);

		$this->assertTrue(
			in_array('zip_only', $fieldsToHide)
		);
	}

	/**
	 * @test
	 */
	public function setZipSubpartVisibilityForShownCityAndHiddenZipAddsZipOnlySubpartToHideFields() {
		$fieldsToHide = array('zip');
		$this->fixture->setZipSubpartVisibility($fieldsToHide);

		$this->assertTrue(
			in_array('zip_only', $fieldsToHide)
		);
	}

	/**
	 * @test
	 */
	public function setZipSubpartVisibilityForHiddenCityAndShownZipDoesNotAddZipOnlySubpartToHideFields() {
		$fieldsToHide = array('city');
		$this->fixture->setZipSubpartVisibility($fieldsToHide);

		$this->assertFalse(
			in_array('zip_only', $fieldsToHide)
		);
	}


	/*
	 * Tests concerning setUserGroupSubpartVisibility
	 */

	/**
	 * @test
	 */
	public function setUserGroupSubpartVisibilityForNonExistingUsergroupAddsUsergroupSubpartToHideFields() {
		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers',
			$this->testingFramework->getAutoIncrement('fe_groups')
		);
		$fieldsToHide = array();

		$this->fixture->setUserGroupSubpartVisibility($fieldsToHide);

		$this->assertTrue(
			in_array('usergroup', $fieldsToHide)
		);
	}

	/**
	 * @test
	 */
	public function setUserGroupSubpartVisibilityForOneAvailableUsergroupAddsUsergroupSubpartToHideFields() {
		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers',
			$this->testingFramework->createFrontEndUserGroup()
		);

		$fieldsToHide = array();
		$this->fixture->setUserGroupSubpartVisibility($fieldsToHide);

		$this->assertTrue(
			in_array('usergroup', $fieldsToHide)
		);
	}

	/**
	 * @test
	 */
	public function setUserGroupSubpartVisibilityForTwoAvailableUsergroupDoesNotAddUsergroupSubpartToHideFields() {
		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers',
			$this->testingFramework->createFrontEndUserGroup() . ',' .
				$this->testingFramework->createFrontEndUserGroup()
		);

		$fieldsToHide = array();
		$this->fixture->setUserGroupSubpartVisibility($fieldsToHide);

		$this->assertFalse(
			in_array('usergroup', $fieldsToHide)
		);
	}


	/*
	 * Tests concerning preprocessFormData
	 */

	/**
	 * @test
	 */
	public function preprocessFormDataForNameHiddenUsesFirstNameAndLastNameAsName() {
		$this->fixture->setConfigurationValue(
			'feUserFieldsToDisplay', 'first_name, last_name'
		);
		$this->fixture->setFormFieldsToShow();

		$formData = $this->fixture->preprocessFormData(array(
			'first_name' => 'foo', 'last_name' => 'bar'
		));

		$this->assertSame(
			'foo bar',
			$formData['name']
		);
	}

	/**
	 * @test
	 */
	public function preprocessFormDataForShownNameFieldUsesValueOfNameField() {
		$this->fixture->setConfigurationValue(
			'feUserFieldsToDisplay', 'name,first_name,last_name'
		);
		$this->fixture->setFormFieldsToShow();

		$formData = $this->fixture->preprocessFormData(array(
			'name' => 'foobar', 'first_name' => 'foo', 'last_name' => 'bar'
		));

		$this->assertSame(
			'foobar',
			$formData['name']
		);
	}

	/**
	 * @test
	 */
	public function preprocessFormDataForUserGroupSetInConfigurationSetsTheUsersGroupInFormData() {
		$userGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$this->fixture->setConfigurationValue(
			'feUserFieldsToDisplay', 'name'
		);
		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers', $userGroupUid
		);
		$this->fixture->setFormFieldsToShow();

		$formData = $this->fixture->preprocessFormData(array('name' => 'bar'));

		$this->assertSame(
			(string) $userGroupUid,
			$formData['usergroup']
		);
	}

	/**
	 * @test
	 */
	public function preprocessFormDataForTwoUserGroupsSetInConfigurationAndOneSelectedInFormSetsTheSelectedUsergroupInFormData() {
		$userGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$userGroupUid2 = $this->testingFramework->createFrontEndUserGroup();
		$this->fixture->setConfigurationValue(
			'feUserFieldsToDisplay', 'name,usergroups'
		);
		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers', $userGroupUid . ',' . $userGroupUid2
		);
		$this->fixture->setFormFieldsToShow();

		$formData = $this->fixture->preprocessFormData(
			array('usergroup' => $userGroupUid)
		);

		$this->assertSame(
			$userGroupUid,
			$formData['usergroup']
		);
	}

	/**
	 * @test
	 */
	public function preprocessFormDataForTwoUserGroupsSetInConfigurationTheGroupFieldHiddenSetsTheUsergroupsFromConfiguration() {
		$userGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$userGroupUid2 = $this->testingFramework->createFrontEndUserGroup();
		$this->fixture->setConfigurationValue(
			'feUserFieldsToDisplay', 'name'
		);
		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers', $userGroupUid . ',' . $userGroupUid2
		);
		$this->fixture->setFormFieldsToShow();

		$formData = $this->fixture->preprocessFormData(array());

		$this->assertSame(
			$userGroupUid . ',' . $userGroupUid2,
			$formData['usergroup']
		);
	}

	/**
	 * @test
	 */
	public function preprocessFormDataForUserGroupSetInConfigurationButNoGroupChosenInFormSetsTheUsersGroupFromConfiguration() {
		$userGroupUid = $this->testingFramework->createFrontEndUserGroup();
		$this->fixture->setConfigurationValue(
			'feUserFieldsToDisplay', 'name,usergroup'
		);
		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers', $userGroupUid
		);
		$this->fixture->setFormFieldsToShow();

		$formData = $this->fixture->preprocessFormData(
			array('name' => 'bar', 'usergroup' => '')
		);

		$this->assertSame(
			(string) $userGroupUid,
			$formData['usergroup']
		);
	}
}