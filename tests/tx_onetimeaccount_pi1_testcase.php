<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2009 Oliver Klee <typo3-coding@oliverklee.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

require_once(t3lib_extMgm::extPath('onetimeaccount') . 'tests/fixtures/class.tx_onetimeaccount_fakePi1.php');

/**
 * Testcase for the pi1 class in the 'onetimeaccount' extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_onetimeaccount_pi1_testcase extends tx_phpunit_testcase {
	/**
	 * @var tx_onetimeaccount_fakePi1
	 */
	private $fixture;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->fixture = new tx_onetimeaccount_fakePi1(
			array(
				'isStaticTemplateLoaded' => 1,
			)
		);
		$this->fixture->cObj = $GLOBALS['TSFE']->cObj;
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////
	// Utility functions
	//////////////////////

	private function skipTestForNoMd5() {
		if(!t3lib_extMgm::isLoaded('sr_feuser_register')
			|| !t3lib_extMgm::isLoaded('kb_md5fepw')
		) {
			$this->markTestSkipped(
				'This test is only applicable if sr_feuser_register and ' .
					'kb_md5fepw are loaded.'
			);
		}
	}


	/////////////////////////////////
	// Tests concerning getFormData
	/////////////////////////////////

	public function testGetFormDataReturnsNonEmptyDataSetViaSetFormData() {
		$this->fixture->setFormData(array('foo' => 'bar'));

		$this->assertEquals(
			'bar',
			$this->fixture->getFormData('foo')
		);
	}


	/////////////////////////////////
	// Tests concerning getUserName
	/////////////////////////////////

	public function test_GetUserName_WithNonEmptyEmail_ReturnsNonEmptyString() {
		$this->fixture->setFormData(array('email' => 'foo@bar.com'));

		$this->assertNotEquals(
			'',
			$this->fixture->getUserName()
		);
	}

	public function test_GetUserName_WithEmailOfExistingUserName_ReturnsDifferentName() {
		$this->testingFramework->createFrontEndUser(
			$this->testingFramework->createFrontEndUserGroup(),
			array('username' => 'foo@bar.com')
		);
		$this->fixture->setFormData(array('email' => 'foo@bar.com'));

		$this->assertNotEquals(
			'foo@bar.com',
			$this->fixture->getUserName()
		);
	}

	public function test_GetUserName_WithEmptyEmail_ReturnsNonEmptyString() {
		$this->fixture->setFormData(array('email' => ''));

		$this->assertNotEquals(
			'',
			$this->fixture->getUserName()
		);
	}

	public function test_GetUserName_WithEmptyEmailAndDefaultUserNameAlreadyExisting_ReturnsNewUniqueUsernameString() {
		$this->testingFramework->createFrontEndUser(
			'', array('username' => 'user')
		);
		$this->fixture->setFormData(array('email' => ''));

		$this->assertNotEquals(
			'user',
			$this->fixture->getUserName()
		);
	}


	/////////////////////////////////
	// Tests concerning getPassword
	/////////////////////////////////

	public function testGetPasswordReturnsPasswordWithEightCharacters() {
		$this->assertEquals(
			8,
			strlen($this->fixture->getPassword())
		);
	}


	////////////////////////////////////////////////
	// Tests concerning getRedirectUrlAndLoginUser
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getRedirectUrlAndLoginUserReturnsRedirectUrl() {
		$_POST['redirect_url'] = 'http://foo.com/';

		$this->assertEquals(
			'http://foo.com/',
			$this->fixture->getRedirectUrlAndLoginUser()
		);
	}

	/**
	 * @test
	 */
	public function getRedirectUrlAndLoginUserWithoutRedirectUrlReturnsFullyQualifiedUrl() {
		$_POST['redirect_url'] = '';

		$this->assertRegExp(
			'/https?:\/\//',
			$this->fixture->getRedirectUrlAndLoginUser()
		);
	}

	/**
	 * @test
	 */
	public function getRedirectUrlAndLoginUserWithoutRedirectUrlIsCurrentUri() {
		$_POST['redirect_url'] = '';

		$this->assertEquals(
			t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'),
			$this->fixture->getRedirectUrlAndLoginUser()
		);
	}

	public function test_GetRedirectUrlAndLoginUser_ForDisabledMd5Passwords_LogsInFrontEndUser() {
		if (t3lib_extMgm::isLoaded('sr_feuser_register')
			&& t3lib_extMgm::isLoaded('kb_md5fepw')) {
			$this->markTestSkipped(
				'This test is only applicable if neither sr_feuser_register ' .
					'nor kb_md5fepw are loaded.'
			);
		}

		$userData = array(
			'username' => 'foo@bar.com', 'password' => '12345678'
		);
		$this->testingFramework->createFrontEndUser('', $userData);
		$this->fixture->setFormData($userData);

		$this->fixture->getRedirectUrlAndLoginUser();

		$this->assertTrue(
			$this->testingFramework->isLoggedIn()
		);
	}

	public function test_GetRedirectUrlAndLoginUser_ForEnabledMd5Passwords_LogsInFrontEndUser() {
		$this->skipTestForNoMd5();

		$userData = array(
			'username' => 'foo@bar.com',
			'password' => md5('12345678'),
		);
		$this->testingFramework->createFrontEndUser('', $userData);

		$userData['password'] = '12345678';
		$this->fixture->setFormData($userData);

		$this->fixture->getRedirectUrlAndLoginUser();

		$this->assertTrue(
			$this->testingFramework->isLoggedIn()
		);
	}

	public function testGetRedirectUrlAndLoginUserSetsOnetimeaccountToOneInUserSession() {
		$userData = array(
			'username' => 'foo@bar.com', 'password' => '12345678'
		);
		$this->testingFramework->createFrontEndUser(
			$this->testingFramework->createFrontEndUserGroup(), $userData
		);
		$this->fixture->setFormData($userData);

		$session = new tx_oelib_FakeSession();
		tx_oelib_Session::setInstance(
			tx_oelib_Session::TYPE_USER, $session
		);

		$this->fixture->getRedirectUrlAndLoginUser();

		$this->assertTrue(
			$session->getAsBoolean('onetimeaccount')
		);
	}


	/////////////////////////////////////////////
	// Tests concerning validateStringField
	/////////////////////////////////////////////

	public function test_ValidateStringField_ForNotRequiredField_ReturnsTrue() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertTrue(
			$this->fixture->validateStringField(
				array('elementName' => 'address')
			)
		);
	}

	public function test_ValidateStringField_ForMissingFieldName_ThrowsException() {
		$this->setExpectedException(
			'Exception', 'The given field name was empty.'
		);

		$this->fixture->validateStringField(array());

	}

	public function test_ValidateStringField_ForNonEmptyRequiredField_ReturnsTrue() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertTrue(
			$this->fixture->validateStringField(
				array('elementName' => 'name', 'value' => 'foo')
			)
		);
	}

	public function test_ValidateStringField_ForEmptyRequiredField_ReturnsFalse() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertFalse(
			$this->fixture->validateStringField(
				array('elementName' => 'name', 'value' => '')
			)
		);
	}


	////////////////////////////////////////////
	// Tests concerning validateIntegerField
	////////////////////////////////////////////

	public function test_ValidateIntegerField_ForRequiredFieldValueZero_ReturnsFalse() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertFalse(
			$this->fixture->validateIntegerField(
				array('elementName' => 'name', 'value' => 0)
			)
		);
	}

	public function test_ValidateIntegerField_ForNonRequiredField_ReturnsTrue() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertTrue(
			$this->fixture->validateIntegerField(
				array('elementName' => 'address')
			)
		);
	}

	public function test_ValidateIntegerField_ForRequiredFieldValueNonZero_ReturnsTrue() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertTrue(
			$this->fixture->validateIntegerField(
				array('elementName' => 'name', 'value' => 1)
			)
		);
	}

	public function test_ValidateIntegerField_ForRequiredFieldValueString_ReturnsFalse() {
		$this->fixture->setConfigurationValue('requiredFeUserFields', 'name');

		$this->assertFalse(
			$this->fixture->validateIntegerField(
				array('elementName' => 'name', 'value' => 'foo')
			)
		);
	}

	public function test_ValidateIntegerField_ForMissingFieldName_ThrowsException() {
		$this->setExpectedException(
			'Exception',
			'The given field name was empty.'
		);

		$this->fixture->validateIntegerField(array());
	}


	/////////////////////////////////////////////
	// Tests concerning getPidForNewUserRecords
	/////////////////////////////////////////////

	public function test_GetPidForNewUserRecords_ForEmptyConfigValue_ReturnsZero() {
		$this->fixture->setConfigurationValue(
			'systemFolderForNewFeUserRecords', ''
		);

		$this->assertEquals(
			0,
			$this->fixture->getPidForNewUserRecords()
		);
	}

	public function test_GetPidForNewUserRecords_ForConfigValueString_ReturnsZero() {
		$this->fixture->setConfigurationValue(
			'systemFolderForNewFeUserRecords', 'foo'
		);

		$this->assertEquals(
			0,
			$this->fixture->getPidForNewUserRecords()
		);
	}

	public function test_GetPidForNewUserRecords_ForConfigValueInteger_ReturnsInteger() {
		$this->fixture->setConfigurationValue(
			'systemFolderForNewFeUserRecords', 42
		);

		$this->assertEquals(
			42,
			$this->fixture->getPidForNewUserRecords()
		);
	}


	////////////////////////////////////
	// Tests concerning listUserGroups
	////////////////////////////////////

	public function test_ListUserGroups_ForExistingAndConfiguredUserGroup_ReturnsGroupTitleAndUid() {
		$userGroupUid = $this->testingFramework->createFrontEndUserGroup(
			array('title' => 'foo')
		);

		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers', $userGroupUid
		);

		$this->assertEquals(
			array(array('caption' => 'foo<br />', 'value' => $userGroupUid)),
			$this->fixture->listUserGroups()
		);
	}

	public function test_ListUserGroups_HtmlSpecialCharsGroupName() {
		$userGroupUid = $this->testingFramework->createFrontEndUserGroup(
			array('title' => 'a&b')
		);

		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers', $userGroupUid
		);

		$this->assertEquals(
			array(array('caption' => 'a&amp;b<br />', 'value' => $userGroupUid)),
			$this->fixture->listUserGroups()
		);
	}

	public function test_ListUserGroups_StringConfiguredAsUserGroup_ReturnsEmptyArray() {
		$this->fixture->setConfigurationValue('groupForNewFeUsers', 'foo');

		$this->assertEquals(
			array(),
			$this->fixture->listUserGroups()
		);
	}

	public function test_ListUserGroups_ForTwoExistingButOnlyOneConfiguredUserGroup_ReturnsOnlyConfiguredGroup() {
		$userGroupUid = $this->testingFramework->createFrontEndUserGroup(
			array('title' => 'foo')
		);
		$this->testingFramework->createFrontEndUserGroup(
			array('title' => 'bar')
		);

		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers', $userGroupUid
		);

		$this->assertEquals(
			array(array('caption' => 'foo<br />', 'value' => $userGroupUid)),
			$this->fixture->listUserGroups()
		);
	}

	public function test_ListUserGroups_ForTwoExistingButAndConfiguredUserGroups_ReturnsBothConfiguredGroup() {
		$userGroupUid1 = $this->testingFramework->createFrontEndUserGroup(
			array('title' => 'foo', 'crdate' => 1)
		);
		$userGroupUid2 = $this->testingFramework->createFrontEndUserGroup(
			array('title' => 'bar', 'crdate' => 2)
		);

		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers', $userGroupUid1 . ', ' . $userGroupUid2
		);

		$this->assertEquals(
			array(
				array('caption' => 'foo<br />', 'value' => $userGroupUid1),
				array('caption' => 'bar<br />', 'value' => $userGroupUid2),
			),
			$this->fixture->listUserGroups()
		);
	}


	////////////////////////////////////
	// Tests concerning listUserGroups
	////////////////////////////////////

	public function test_ListUserGroups_ForGroupForNewUsersEmpty_ReturnsEmptyArray() {
		$this->fixture->setConfigurationValue('groupForNewFeUsers', '');

		$this->assertEquals(
			array(),
			$this->fixture->listUserGroups()
		);
	}


	//////////////////////////////////////////////////
	// Tests concerning setAllNamesSubpartVisibility
	//////////////////////////////////////////////////

	public function test_SetAllNamesSubpartVisibility_ForAllNameRelatedFieldsHidden_AddsAllNamesSubpartToHideFields() {
		$fieldsToHide = array('name', 'title', 'gender', 'first_name', 'last_name');
		$this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

		$this->assertTrue(
			in_array('all_names', $fieldsToHide)
		);
	}

	public function test_SetAllNamesSubpartVisibility_ForVisibleNameField_DoesNotAddAllNamesSubpartToHideFields() {
		$fieldsToHide = array('title', 'gender', 'first_name', 'last_name');
		$this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

		$this->assertFalse(
			in_array('all_names', $fieldsToHide)
		);
	}

	public function test_SetAllNamesSubpartVisibility_ForVisibleFirstNameField_DoesNotAddAllNamesSubpartToHideFields() {
		$fieldsToHide = array('name', 'title', 'gender', 'last_name');
		$this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

		$this->assertFalse(
			in_array('all_names', $fieldsToHide)
		);
	}

	public function test_SetAllNamesSubpartVisibility_ForVisibleLastNameField_DoesNotAddAllNamesSubpartToHideFields() {
		$fieldsToHide = array('name', 'title', 'gender', 'first_name');
		$this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

		$this->assertFalse(
			in_array('all_names', $fieldsToHide)
		);
	}

	public function test_SetAllNamesSubpartVisibility_ForVisibleTitleField_DoesNotAddAllNamesSubpartToHideFields() {
		$fieldsToHide = array('name', 'gender', 'first_name', 'last_name');
		$this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

		$this->assertFalse(
			in_array('all_names', $fieldsToHide)
		);
	}

	public function test_SetAllNamesSubpartVisibility_ForVisibleGenderField_DoesNotAddAllNamesSubpartToHideFields() {
		$fieldsToHide = array('name', 'title', 'first_name', 'last_name');
		$this->fixture->setAllNamesSubpartVisibility($fieldsToHide);

		$this->assertFalse(
			in_array('all_names', $fieldsToHide)
		);
	}


	/////////////////////////////////////////////
	// Tests concerning setZipSubpartVisibility
	/////////////////////////////////////////////

	public function test_SetZipSubpartVisibility_ForHiddenCityAndZip_AddsZipOnlySubpartToHideFields() {
		$fieldsToHide = array('zip', 'city');
		$this->fixture->setZipSubpartVisibility($fieldsToHide);

		$this->assertTrue(
			in_array('zip_only', $fieldsToHide)
		);
	}

	public function test_SetZipSubpartVisibility_ForShownCityAndZip_AddsZipOnlySubpartToHideFields() {
		$fieldsToHide = array();
		$this->fixture->setZipSubpartVisibility($fieldsToHide);

		$this->assertTrue(
			in_array('zip_only', $fieldsToHide)
		);
	}

	public function test_SetZipSubpartVisibility_ForShownCityAndHiddenZip_AddsZipOnlySubpartToHideFields() {
		$fieldsToHide = array('zip');
		$this->fixture->setZipSubpartVisibility($fieldsToHide);

		$this->assertTrue(
			in_array('zip_only', $fieldsToHide)
		);
	}

	public function test_SetZipSubpartVisibility_ForHiddenCityAndShownZip_DoesNotAddZipOnlySubpartToHideFields() {
		$fieldsToHide = array('city');
		$this->fixture->setZipSubpartVisibility($fieldsToHide);

		$this->assertFalse(
			in_array('zip_only', $fieldsToHide)
		);
	}


	///////////////////////////////////////////////////
	// Tests concerning setUsergroupSubpartVisibility
	///////////////////////////////////////////////////

	public function test_SetUsergroupSubpartVisibility_ForNonExistingUsergroup_AddsUsergroupSubpartToHideFields() {
		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers',
			$this->testingFramework->getAutoIncrement('fe_groups')
		);
		$fieldsToHide = array();

		$this->fixture->setUsergroupSubpartVisibility($fieldsToHide);

		$this->assertTrue(
			in_array('usergroup', $fieldsToHide)
		);
	}

	public function test_SetUsergroupSubpartVisibility_ForOneAvailableUsergroup_AddsUsergroupSubpartToHideFields() {
		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers',
			$this->testingFramework->createFrontEndUserGroup()
		);

		$fieldsToHide = array();
		$this->fixture->setUsergroupSubpartVisibility($fieldsToHide);

		$this->assertTrue(
			in_array('usergroup', $fieldsToHide)
		);
	}

	public function test_SetUsergroupSubpartVisibility_ForTwoAvailableUsergroup_DoesNotAddUsergroupSubpartToHideFields() {
		$this->fixture->setConfigurationValue(
			'groupForNewFeUsers',
			$this->testingFramework->createFrontEndUserGroup() . ',' .
				$this->testingFramework->createFrontEndUserGroup()
		);

		$fieldsToHide = array();
		$this->fixture->setUsergroupSubpartVisibility($fieldsToHide);

		$this->assertFalse(
			in_array('usergroup', $fieldsToHide)
		);
	}

	/////////////////////////////////////
	// Tests concerning createChallenge
	/////////////////////////////////////

	public function test_CreateChallenge_ForLoadedSrFeuserRegisterAndNotLoadedKbMd5pw_ReturnsNonEmptyString() {
		if(!t3lib_extMgm::isLoaded('sr_feuser_register')
			|| t3lib_extMgm::isLoaded('kb_md5fepw')) {
			$this->markTestSkipped(
					'This test is only applicable if sr_feuser_register is ' .
						'loaded and kb_md5fepw is not loaded.'
			);
		}

		$this->assertNotEquals(
			'',
			$this->fixture->createChallenge()
		);
	}

	public function test_CreateChallenge_ForLoadedSrFeuserRegisterAndLoadedKbMd5pw_ReturnsNonEmptyString() {
		$this->skipTestForNoMd5();

		$this->assertNotEquals(
			'',
			$this->fixture->createChallenge()
		);
	}

}
?>