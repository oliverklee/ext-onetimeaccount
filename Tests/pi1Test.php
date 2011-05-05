<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2011 Oliver Klee <typo3-coding@oliverklee.de>
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

/**
 * Testcase for the tx_onetimeaccount_pi1 class in the "onetimeaccount"
 * extension.
 *
 * @package TYPO3
 * @subpackage tx_seminars
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_onetimeaccount_pi1Test extends tx_phpunit_testcase {
	/**
	 * @var tx_onetimeaccount_fakePi1
	 */
	private $fixture = NULL;
	/**
	 * @var tx_oelib_testingFramework
	 */
	private $testingFramework = NULL;

	public function setUp() {
		$this->testingFramework = new tx_oelib_testingFramework('tx_seminars');
		$this->testingFramework->createFakeFrontEnd();

		$this->fixture = new tx_onetimeaccount_Tests_Fixtures_FakePi1(
			array(
				'isStaticTemplateLoaded' => 1,
				'userNameSource' => 'email',
			)
		);
		$this->fixture->cObj = $GLOBALS['TSFE']->cObj;

		$configurationProxy = tx_oelib_configurationProxy::getInstance('onetimeaccount');
		$configurationProxy->setAsBoolean('enableConfigCheck', FALSE);
		$configurationProxy->setAsBoolean('enableLogging', FALSE);
	}

	public function tearDown() {
		$this->testingFramework->cleanUp();

		$this->fixture->__destruct();
		unset($this->fixture, $this->testingFramework);
	}


	//////////////////////
	// Utility functions
	//////////////////////

	/**
	 * Skips the test if there is not MD5 password extension installed.
	 *
	 * @return void
	 */
	private function skipTestForNoMd5() {
		if (!t3lib_extMgm::isLoaded('sr_feuser_register') || !t3lib_extMgm::isLoaded('kb_md5fepw')) {
			$this->markTestSkipped(
				'This test is only applicable if sr_feuser_register and ' .
					'kb_md5fepw are loaded.'
			);
		}
	}

	/**
	 * Extracts the URL which is encoded in $url in a serialized array which
	 * is encoded in the "data" GET parameter.
	 *
	 * @param string $url
	 *        URL to that contains the data to decode, must not be empty
	 *
	 * @return string the encoded URL, will be empty if no URL could be found
	 */
	private function extractEncodedUrlFromUrl($url) {
		$matches = array();
		preg_match(
			'/(^\?|&)(data=)([^&]+)(&|$)/',
			$url,
			$matches
		);
		if (empty($matches)) {
			return '';
		}

		$data = unserialize(base64_decode(rawurldecode($matches[3])));
		return $data['url'];
	}


	/////////////////////////////////
	// Tests concerning getFormData
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getFormDataReturnsNonEmptyDataSetViaSetFormData() {
		$this->fixture->setFormData(array('foo' => 'bar'));

		$this->assertEquals(
			'bar',
			$this->fixture->getFormData('foo')
		);
	}


	///////////////////////////////////////////
	// Tests concerning createInitialUserName
	///////////////////////////////////////////

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


	/////////////////////////////////
	// Tests concerning getUserName
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getUserNameWithNonEmptyEmailReturnsNonEmptyString() {
		$this->fixture->setFormData(array('email' => 'foo@example.com'));

		$this->assertNotEquals(
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

		$this->assertNotEquals(
			'foo@example.com',
			$this->fixture->getUserName()
		);
	}

	/**
	 * @test
	 */
	public function getUserNameWithEmptyEmailReturnsNonEmptyString() {
		$this->fixture->setFormData(array('email' => ''));

		$this->assertNotEquals(
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

		$this->assertNotEquals(
			'user',
			$this->fixture->getUserName()
		);
	}


	/////////////////////////////////
	// Tests concerning getPassword
	/////////////////////////////////

	/**
	 * @test
	 */
	public function getPasswordReturnsPasswordWithEightCharacters() {
		$this->assertEquals(
			8,
			strlen($this->fixture->getPassword())
		);
	}


	////////////////////////////////////////////////
	// Tests concerning createRedirectUrl
	////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function createRedirectUrlReturnsEidUrl() {
		$GLOBALS['_POST']['redirect_url'] = '';

		$this->assertRegExp(
			'/https?:\/\/.+\/index\.php\?eID=onetimeaccount&data=/',
			$this->fixture->createRedirectUrl()
		);
	}

	/**
	 * @test
	 */
	public function createRedirectUrlReturnsEncodedRedirectUrl() {
		$GLOBALS['_POST']['redirect_url'] = 'http://foo.com/';

		$this->assertEquals(
			'http://foo.com/',
			$this->extractEncodedUrlFromUrl(
				$this->fixture->createRedirectUrl()
			)
		);
	}

	/**
	 * @test
	 */
	public function createRedirectUrlWithoutRedirectUrlIsCurrentUri() {
		$GLOBALS['_POST']['redirect_url'] = '';

		$this->assertEquals(
			t3lib_div::getIndpEnv('TYPO3_REQUEST_URL'),
			$this->extractEncodedUrlFromUrl(
				$this->fixture->createRedirectUrl()
			)
		);
	}


	/////////////////////////////////////////////
	// Tests concerning validateStringField
	/////////////////////////////////////////////

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


	////////////////////////////////////////////
	// Tests concerning validateIntegerField
	////////////////////////////////////////////

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


	/////////////////////////////////////////////
	// Tests concerning getPidForNewUserRecords
	/////////////////////////////////////////////

	/**
	 * @test
	 */
	public function getPidForNewUserRecordsForEmptyConfigValueReturnsZero() {
		$this->fixture->setConfigurationValue(
			'systemFolderForNewFeUserRecords', ''
		);

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
			42,
			$this->fixture->getPidForNewUserRecords()
		);
	}


	////////////////////////////////////
	// Tests concerning listUserGroups
	////////////////////////////////////

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

		$this->assertEquals(
			array(array('caption' => 'foo<br />', 'value' => $userGroupUid)),
			$this->fixture->listUserGroups()
		);
	}

	/**
	 * @test
	 */
	public function listUserGroupsHtmlSpecialCharsGroupName() {
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

	/**
	 * @test
	 */
	public function listUserGroupsForStringConfiguredAsUserGroupReturnsEmptyArray() {
		$this->fixture->setConfigurationValue('groupForNewFeUsers', 'foo');

		$this->assertEquals(
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

		$this->assertEquals(
			array(array('caption' => 'foo<br />', 'value' => $userGroupUid)),
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

	/**
	 * @test
	 */
	public function listUserGroupsForGroupForNewUsersEmptyReturnsEmptyArray() {
		$this->fixture->setConfigurationValue('groupForNewFeUsers', '');

		$this->assertEquals(
			array(),
			$this->fixture->listUserGroups()
		);
	}


	//////////////////////////////////////////////////
	// Tests concerning setAllNamesSubpartVisibility
	//////////////////////////////////////////////////

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


	/////////////////////////////////////////////
	// Tests concerning setZipSubpartVisibility
	/////////////////////////////////////////////

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


	///////////////////////////////////////////////////
	// Tests concerning setUsergroupSubpartVisibility
	///////////////////////////////////////////////////

	/**
	 * @test
	 */
	public function setUsergroupSubpartVisibilityForNonExistingUsergroupAddsUsergroupSubpartToHideFields() {
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

	/**
	 * @test
	 */
	public function setUsergroupSubpartVisibilityForOneAvailableUsergroupAddsUsergroupSubpartToHideFields() {
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

	/**
	 * @test
	 */
	public function setUsergroupSubpartVisibilityForTwoAvailableUsergroupDoesNotAddUsergroupSubpartToHideFields() {
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

	/**
	 * @test
	 */
	public function createChallengeForLoadedSrFeuserRegisterAndNotLoadedKbMdFivePasswordReturnsNonEmptyString() {
		if (!t3lib_extMgm::isLoaded('sr_feuser_register') || t3lib_extMgm::isLoaded('kb_md5fepw')) {
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

	/**
	 * @test
	 */
	public function createChallengeForLoadedSrFeuserRegisterAndLoadedKbMdFivePasswordReturnsNonEmptyString() {
		$this->skipTestForNoMd5();

		$this->assertNotEquals(
			'',
			$this->fixture->createChallenge()
		);
	}


	////////////////////////////////////////
	// Tests concerning preprocessFormData
	////////////////////////////////////////

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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
			$userGroupUid,
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

		$this->assertEquals(
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

		$this->assertEquals(
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

		$this->assertEquals(
			$userGroupUid,
			$formData['usergroup']
		);
	}
}
?>