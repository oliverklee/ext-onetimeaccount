<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Oliver Klee <typo3-coding@oliverklee.de>
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

	public function testGetUserNameWithNonEmptyEmailReturnsNonEmptyString() {
		$this->fixture->setFormData(array('email' => 'foo@bar.com'));

		$this->assertNotEquals(
			'',
			$this->fixture->getUserName()
		);
	}

	public function testGetUserNameWithEmailOfExistingUserNameReturnsDifferentName() {
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
}
?>