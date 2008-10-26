<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2008 Oliver Klee <typo3-coding@oliverklee.de>
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

require_once(PATH_formidableapi);

require_once(t3lib_extMgm::extPath('oelib') . 'tx_oelib_commonConstants.php');
require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_templatehelper.php');
require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_db.php');
require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_session.php');

require_once(t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');

/**
 * Plugin 'One-time FE account creator' for the 'onetimeaccount' extension.
 *
 * @package TYPO3
 * @subpackage tx_onetimeaccount
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_onetimeaccount_pi1 extends tx_oelib_templatehelper {
	/**
	 * @var string same as class name
	 */
	public $prefixId = 'tx_onetimeaccount_pi1';
	/**
	 * @var string path to this script relative to the extension dir
	 */
	public $scriptRelPath = 'pi1/class.tx_onetimeaccount_pi1.php';
	/**
	 * @var string the extension key
	 */
	public $extKey = 'onetimeaccount';

	/**
	 * @var tx_ameosformidable FORMidable object that creates the edit form
	 */
	protected $form = null;

	/**
	 * @var array names of the form fields to show
	 */
	private $formFieldsToShow = array();

	/**
	 * @var array names of the form fields that are required to be filled in
	 */
	private $requiredFormFields = array();

	/**
	 * @var tx_staticinfotables_pi1
	 */
	private $staticInfo = null;

	/**
	 * Frees as much memory that has been used by this object as possible.
	 */
	public function __destruct() {
		unset($this->form, $this->staticInfo);
		parent::__destruct();
	}

	/**
	 * Creates the plugin output.
	 *
	 * @param string (ignored)
	 * @param array the plug-in configuration
	 *
	 * @return string HTML output of the plug-in
	 */
	public function main($content, array $configuration) {
		$this->init($configuration);
		$this->pi_initPIflexForm();

		// disables caching
		$this->pi_USER_INT_obj = 1;

		$this->initializeFormFields();
		$this->initializeForm();

		$result = $this->renderForm();
		$result .= $this->checkConfiguration();

		return $this->pi_wrapInBaseClass($result);
	}

	/**
	 * Creates and initializes the FORMidable object.
	 */
	protected function initializeForm() {
		$this->form = t3lib_div::makeInstance('tx_ameosformidable');
		$this->form->init(
			$this,
			t3lib_extmgm::extPath($this->extKey).'pi1/onetimeaccount_pi1.xml',
			// false = only create new records
			false
		);
	}

	/**
	 * Initializes which form fields should be shown and which are required.
	 */
	private function initializeFormFields() {
		$this->setFormFieldsToShow();
		$this->setRequiredFormFields();
	}

	/**
	 * Reads the list of form fields to show from the configuration and stores
	 * it in $this->formFieldsToShow.
	 */
	private function setFormFieldsToShow() {
		$this->formFieldsToShow = t3lib_div::trimExplode(
			',',
			$this->getConfValueString('feUserFieldsToDisplay', 's_general')
		);
	}

	/**
	 * Reads the list of required form fields from the configuration and stores
	 * it in $this->requiredFormFields.
	 */
	private function setRequiredFormFields() {
		$this->requiredFormFields = t3lib_div::trimExplode(
			',',
			$this->getConfValueString('requiredFeUserFields', 's_general')
		);
	}

	/**
	 * Gets the path to the HTML template as set in the TS setup or flexforms.
	 * The returned path will always be an absolute path in the file system;
	 * EXT: references will automatically get resolved.
	 *
	 * @return string the path to the HTML template as an absolute path in
	 *                the file system, will not be empty in a correct
	 *                configuration
	 */
	public function getTemplatePath() {
		return t3lib_div::getFileAbsFileName(
			$this->getConfValueString('templateFile', 's_template_special', true)
		);
	}

	/**
	 * Creates the HTML output of the form.
	 *
	 * @return string HTML of the form
	 */
	private function renderForm() {
		$rawForm = $this->form->_render();

		$this->processTemplate($rawForm);
		$this->setLabels();
		$this->hideUnusedFormFields();

		return $this->getSubpart();
	}

	/**
	 * Hides form fields that are disabled via TS setup from the templating
	 * process.
	 */
	private function hideUnusedFormFields() {
		static $availableFormFields = array(
			'company',
			'gender',
			'title',
			'name',
			'first_name',
			'last_name',
			'address',
			'zip',
			'city',
			'zone',
			'country',
			'static_info_country',
			'email',
			'www',
			'telephone',
			'fax',
			'date_of_birth',
			'status',
			'module_sys_dmail_html',
			'usergroup',
			'comments',
		);

		$formFieldsToHide = array_diff(
			$availableFormFields,
			$this->formFieldsToShow
		);

		// The "usergroup" field is a special case because it might also be
		// hidden if there are less than two user groups available
		if (!$this->hasAtLeastTwoUserGroups()) {
			// We might be hiding the field two times, but that does no harm.
			$formFieldsToHide[] = 'usergroup';
		}

		$this->hideSubparts(
			implode(',', $formFieldsToHide),
			'wrapper'
		);
	}

	/**
	 * Checks whether a form field should be displayed (and evaluated) at all.
	 * This is specified via TS setup (or flexforms) using the
	 * "feUserFieldsToDisplay" variable.
	 * Radiobuttons to choose user groups are only shown if there is more than
	 * one value to display.
	 *
	 * @param array the contents of the "params" child of the userobj
	 *              node as key/value pairs (used for retrieving the current
	 *              form field name)
	 *
	 * @return boolean true if the current form field should be displayed,
	 *                 false otherwise
	 */
	public function isFormFieldEnabled(array $parameters) {
		$key = $parameters['elementname'];
		$result = in_array($key, $this->formFieldsToShow);
		if ($key == 'usergroup') {
			$result = $result && $this->hasAtLeastTwoUserGroups();

		}
		return $result;
	}

	/**
	 * Provides a localized list of localized country names from static_tables.
	 *
	 * If $parameters['alpha3'] is set, the alpha3 codes will be used as form
	 * values. Otherwise, the localized country names will be used as values.
	 *
	 * @param mixed (unused)
	 * @param array contents of the "params" XML child of the userobj
	 *              node (needs to contain an element with the key "key")
	 *
	 * @return array a list of localized country names from static_tables
	 *               as an array with the keys "caption" (for the
	 *               localized title) and "value" (either the country's
	 *               alpha3 code or the localized name)
	 */
	public function populateListCountries($unused, array $parameters) {
		$this->initStaticInfo();
		$allCountries = $this->staticInfo->initCountries(
			'ALL', $this->staticInfo->getCurrentLanguage(), true
		);

		$result = array();
		// Add an empty item at the top so we won't have Afghanistan (the first
		// entry) pre-selected for empty values.
		$result[] = array(
			'caption' => '&nbsp;',
			'value' => ''
		);

		foreach ($allCountries as $alpha3Code => $currentCountryName) {
			$result[] = array(
				'caption' => $currentCountryName,
				'value' => (isset($parameters['alpha3']))
					? $alpha3Code : $currentCountryName
			);
		}

		return $result;
	}

	/**
	 * Returns the default country as alpha3 code or localized string.
	 *
	 * If $parameters['alpha3'] is set, the alpha3 code will be used as return
	 * value. Otherwise, the localized country name will be used as return value.
	 *
	 * @param mixed (unused)
	 * @param array contents of the "params" XML child of the userobj
	 *              node (needs to contain an element with the key "key")
	 *
	 * @return string the default country (either the country's alpha3
	 *                code or the localized name)
	 */
	public function getDefaultCountry($unused, array $parameters) {
		$this->initStaticInfo();
		$typoScriptPluginSetup = $GLOBALS['TSFE']->tmpl->setup['plugin.'];
		$staticInfoSetup = $typoScriptPluginSetup['tx_staticinfotables_pi1.'];
		$defaultCountryCode = $staticInfoSetup['countryCode'];

		if ($parameters['alpha3']) {
			$result = $defaultCountryCode;
		} else {
			$result = tx_staticinfotables_div::getTitleFromIsoCode(
				'static_countries', $defaultCountryCode,
				$this->staticInfo->getCurrentLanguage(), true
			);
		}

		return $result;
	}

	/**
	 * Creates and initializes $this->staticInfo (if that hasn't been done yet).
	 */
	private function initStaticInfo() {
		if (!$this->staticInfo) {
			$this->staticInfo
				= t3lib_div::makeInstance('tx_staticinfotables_pi1');
			$this->staticInfo->init();
		}
	}

	/**
	 * Gets the PID of the system folder in which new FE user records will be
	 * stored.
	 *
	 * @return integer the PID of the page where FE-created events will be stored
	 */
	public function getPidForNewUserRecords() {
		return $this->getConfValueInteger(
			'systemFolderForNewFeUserRecords',
			's_general'
		);
	}

	/**
	 * Returns the URL that has been set via the GET parameter "redirect_url".
	 * If this parameter has not been set or is empty, an empty string will be
	 * returned.
	 *
	 * In addition, the entered FE user will be automatically logged in, and
	 * the key "onetimeaccount" with the value "1" will be written to the FE
	 * user session.
	 *
	 * @return string the URL set as GET parameter (or an empty string if there is no such GET parameter)
	 */
	public function getRedirectUrlAndLoginUser() {
		$result = t3lib_div::_GP('redirect_url');

		if (empty($result)) {
			// Redirect to the current page if no redirect URL is provided.
			$result = t3lib_div::locationHeaderUrl(
				$this->cObj->typoLink_URL(
					array('parameter' => $GLOBALS['TSFE']->id)
				)
			);
		}

		$userName = $this->getFormData('username');
		// The array key "uident" is required by the compareUident function.
		$loginData = array(
			'uident'=> $this->getFormData('password')
		);

		$GLOBALS['TSFE']->fe_user->checkPid = false;
		$authenticationInformation = $GLOBALS['TSFE']->fe_user->getAuthInfoArray();
		$user = $GLOBALS['TSFE']->fe_user->fetchUserRecord(
			$authenticationInformation['db_user'],
			$userName
		);

		$isLoginOk = $GLOBALS['TSFE']->fe_user->compareUident($user, $loginData);
		if ($isLoginOk) {
			$GLOBALS['TSFE']->fe_user->createUserSession($user);
			$GLOBALS['TSFE']->loginUser = 1;
			$GLOBALS['TSFE']->fe_user->start();

			tx_oelib_session::getInstance(tx_oelib_session::TYPE_USER)
				->setAsBoolean($this->extKey, true);
		}

		return $result;
	}

	/**
	 * Gets the entered form data for the field $key.
	 *
	 * @param string key of the field to retrieve, must not be empty and
	 *               must refer to an existing form field
	 *
	 * @return mixed data for the requested form element
	 */
	protected function getFormData($key) {
		return $this->form->oDataHandler->_getThisFormData($key);
	}

	/**
	 * Creates a unique FE user name. It consists of the entered e-mail address.
	 * If a user with that user name already exists, a number will be appended.
	 *
	 * @return string a user name, will not be empty
	 */
	public function getUserName() {
		$enteredEmail = $this->getFormData('email');
		$numberToAppend = 1;
		$result = $enteredEmail;

		// Modify the user name until we have a unique user name.
		while ($GLOBALS['TSFE']->fe_user->getRawUserByName($result)) {
			$result = $enteredEmail.'-'.$numberToAppend;
			$numberToAppend++;
		}

		return $result;
	}

	/**
	 * Creates a random 8-character password, consisting of digits, uppercase
	 * and lowercase characters and some special chars.
	 *
	 * @return string a random 8 character password
	 */
	public function getPassword() {
		$result = '';

		$availableCharacters
			= 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ'
				.'0123456789!§$%&/()=?*+#,;.:-_<>';
		$indexOfLastCharacter = strlen($availableCharacters) - 1;

		for ($i = 0; $i < 8; $i++) {
			$result .= substr(
				$availableCharacters,
				mt_rand(0, $indexOfLastCharacter),
				1
			);
		}

		return $result;
	}

	/**
	 * Gets the form data and adds the user group(s) from the BE configuration
	 * if the form field to choose a user group in the FE is disabled.
	 *
	 * @param array entered form data, may be empty
	 *
	 * @return array returns form data: If choosing user groups in in FE
	 *               is disabled, the user group(s) of groupForNewFeUsers
	 *               are added to the form data, otherwise it is returned
	 *               without modifications.
	 */
	public function setCurrentUserGroup(array $formData) {
		$result = $formData;

		if (!$this->isFormFieldEnabled(array('elementname' => 'usergroup'))) {
			$result['usergroup'] = $this->getConfValueString(
				'groupForNewFeUsers',
				's_general');
		}

		return $result;
	}

	/**
	 * Returns the UID of the first user group shown in the FE. If there are no
	 * user groups, the result will be zero.
	 *
	 * @return integer UID of the first user group
	 */
	 public function getUidOfFirstUserGroup() {
	 	$userGroups = $this->getUncheckedUidsOfAllowedUserGroups();

	 	return intval($userGroups[0]);
	 }

	/**
	 * Returns an array of user groups choosable in the FE, will not be empty if
	 * configured correctly.
	 *
	 * @return array lists user groups choosable in the FE, will not be
	 *               empty if configured correctly
	 */
	public function listUserGroups() {
		$result = array();
		$listOfUserGroupUids = $this->getConfValueString(
			'groupForNewFeUsers',
			's_general'
		);

		if (preg_match('/^([0-9]+(,( *)[0-9]+)*)?$/', $listOfUserGroupUids)) {
			$allUserGroups = array();
			$userGroupUids = $this->getUncheckedUidsOfAllowedUserGroups();
			$dbResult = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
				'uid, title',
				'fe_groups',
				'uid IN(' . $listOfUserGroupUids . ')' .
					tx_oelib_db::enableFields('fe_groups')
			);
			if (!$dbResult) {
				throw new Exception(DATABASE_QUERY_ERROR);
			}

			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($dbResult)) {
				$allUserGroups[$row['uid']] = $row['title'];
			}
			foreach ($userGroupUids as $currentUid) {
				$result[] = array(
					'caption' => $allUserGroups[$currentUid].'<br />',
					'value' => $currentUid
				);
			}
			$GLOBALS['TYPO3_DB']->sql_free_result($dbResult);
		};

		return $result;
	}

	/**
	 * Gets an array of the value for groupForNewFeUsers from flexforms or TS setup.
	 * The array will contain the UIDs of FE user groups, at least an empty string.
	 *
	 * @return array array of the flexforms or TS setup entry for
	 *               groupForNewFeUsers
	 */
	 public function getUncheckedUidsOfAllowedUserGroups() {
		 return explode(
			',',
		 	$this->getConfValueString('groupForNewFeUsers', 's_general')
		);
	 }

	/**
	 * Checks whether a radiobutton in a radiobutton group is selected.
	 *
	 * @param array the currently selected value in an associative array
	 *              with the key 'value'
	 *
	 * @return boolean true if a radiobutton is selected or if the form
	 *                 field is hidden, false if none is selected although
	 *                 the field is visible
	 */
	public function isRadiobuttonSelected(array $radiogroupValue) {
		if (!$this->isFormFieldEnabled(array('elementname' => 'usergroup'))) {
			return true;
		}

		$allowedValues = $this->getUncheckedUidsOfAllowedUserGroups();

		return in_array($radiogroupValue['value'], $allowedValues);
	}

	/**
	 * Checks whether we have at least two allowed user groups.
	 *
	 * @return boolean true if we have at least two allowed user groups,
	 *                 false otherwise
	 */
	private function hasAtLeastTwoUserGroups() {
		return (count($this->listUserGroups()) > 1);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/onetimeaccount/pi1/class.tx_onetimeaccount_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/onetimeaccount/pi1/class.tx_onetimeaccount_pi1.php']);
}
?>