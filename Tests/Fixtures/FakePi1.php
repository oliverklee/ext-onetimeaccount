<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008-2010 Oliver Klee <typo3-coding@oliverklee.de>
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

require_once(t3lib_extMgm::extPath('onetimeaccount') . 'pi1/class.tx_onetimeaccount_pi1.php');

/**
 * Fake version of the plugin "One-time FE account creator" for the
 * "onetimeaccount" extension.
 *
 * @package TYPO3
 * @subpackage tx_onetimeaccount
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_onetimeaccount_Tests_Fixtures_FakePi1 extends tx_onetimeaccount_pi1 {
	/**
	 * @var array the simulated form date
	 */
	private $formData = array();

	/**
	 * Gets the simulated form data for the field $key.
	 *
	 * @param string key of the field to retrieve, must not be empty and
	 *               must refer to an existing form field
	 *
	 * @return mixed data for the requested form element or an empty
	 *               string if the form field is not set
	 */
	public function getFormData($key) {
		if (!isset($this->formData[$key])) {
			return '';
		}

		return $this->formData[$key];
	}

	/**
	 * Sets the form data.
	 *
	 * @param array form data to set as key/value pairs, may be empty
	 */
	public function setFormData(array $formData) {
		$this->formData = $formData;
	}

	/**
	 * Checks if the 'all_names' subpart containing the names label and
        * the name related fields must be hidden.
	 *
	 * The all_names subpart will be hidden if all name related fields are
	 * hidden. These are: 'title', 'name', 'first_name', 'last_name' and
	 * 'gender'.
	 *
	 * @param array the form fields which should be hidden, may be empty
	 */
	public function setAllNamesSubpartVisibility(array &$formFieldsToHide) {
		parent::setAllNamesSubpartVisibility($formFieldsToHide);
	}

	/**
	 * Checks if the zip_only subpart must be shown.
	 *
	 * The zip_only subpart must be shown if the zip is visible but the city
	 * is not.
	 *
	 * @param array the form fields which should be hidden, may be empty
	 */
	public function setZipSubpartVisibility(array &$formFieldsToHide) {
		parent::setZipSubpartVisibility($formFieldsToHide);
	}

	/**
	 * Checks if the usergroup subpart can be hidden.
	 *
	 * The "usergroup" field is a special case because it might also be
	 * hidden if there are less than two user groups available
	 *
	 * @param array the form fields which should be hidden, may be empty
	 */
	public function setUsergroupSubpartVisibility(array &$formFieldsToHide) {
		parent::setUsergroupSubpartVisibility($formFieldsToHide);
	}

	/**
	 * Generates the challenge for the MD5 passwords.
	 *
	 * Before calling this function, it must be ensured that sr_feuser_register
	 * is loaded.
	 *
	 * @return string the challenge value to insert, will be empty if neither
	 *                kb_md5password is loaded nor felogin is set to use MD5
	 *                passwords
	 */
	public function createChallenge() {
		return parent::createChallenge();
	}

	/**
	 * Reads the list of form fields to show from the configuration and stores
	 * it in $this->formFieldsToShow.
	 */
	public function setFormFieldsToShow() {
		parent::setFormFieldsToShow();
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/onetimeaccount/pi1/tests/fixtures/tx_onetimeaccount_fakePi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/onetimeaccount/pi1/tests/fixtures/tx_onetimeaccount_fakePi1.php']);
}
?>