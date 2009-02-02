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

	public function testGetRedirectUrlAndLoginUserLogsInFrontEndUser() {
		$userData = array(
			'username' => 'foo@bar.com', 'password' => '12345678'
		);
		$this->testingFramework->createFrontEndUser(
			$this->testingFramework->createFrontEndUserGroup(), $userData
		);
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
}
?>