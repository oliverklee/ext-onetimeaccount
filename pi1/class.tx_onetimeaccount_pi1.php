<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Oliver Klee <typo3-coding@oliverklee.de>
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

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_templatehelper.php');
require_once(t3lib_extMgm::extPath('ameos_formidable').'api/class.tx_ameosformidable.php');

if (is_file(
	t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php'
)) {
	require_once(t3lib_extMgm::extPath('static_info_tables').'pi1/class.tx_staticinfotables_pi1.php');
} else {
	// Use sr_static_info as a fallback for TYPO3 < 4.x.
	require_once(t3lib_extMgm::extPath('sr_static_info').'pi1/class.tx_srstaticinfo_pi1.php');
}

/**
 * Plugin 'One-time FE account creator' for the 'onetimeaccount' extension.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 * @package	TYPO3
 * @subpackage	tx_onetimeaccount
 */
class tx_onetimeaccount_pi1 extends tx_oelib_templatehelper {
	var $prefixId = 'tx_onetimeaccount_pi1';
	var $scriptRelPath = 'pi1/class.tx_onetimeaccount_pi1.php';
	var $extKey = 'onetimeaccount';

	/** Formidable object that creates the edit form. */
	var $form = null;

	/** the names of the form fields to show */
	var $formFieldsToShow = array();

	/** the names of the form fields that are required to be filled in*/
	var $requiredFormFields = array();

	/** an instance of tx_staticinfotables_pi1 */
	var $staticInfo = null;

	/**
	 * Creates the plugin output.
	 *
	 * @param	string		(ignored)
	 * @param	array		the plug-in configuration
	 *
	 * @return	string		HTML output of the plug-in
	 */
	function main($content, $conf)	{
		$this->init($conf);
		$this->pi_initPIflexForm();

		$this->addCssToPageHeader();

		// disable caching
		$this->pi_USER_INT_obj = 1;

		$this->initializeForm();

		$result = $this->renderForm();
		$result .= $this->checkConfiguration();

		return $this->pi_wrapInBaseClass($result);
	}

	/**
	 * Initializes the FORMidable object and all related settings.
	 *
	 * @access	protected
	 */
	function initializeForm() {
		$this->setFormFieldsToShow();
		$this->setRequiredFormFields();

		$this->form =& t3lib_div::makeInstance('tx_ameosformidable');
		$this->form->init(
			$this,
			t3lib_extmgm::extPath($this->extKey).'pi1/onetimeaccount_pi1.xml',
			// false = only create new records
			false
		);

		return;
	}

	/**
	 * Reads the list of form fields to show from the configuration and stores
	 * it in $this->formFieldsToShow.
	 *
	 * @access	private
	 */
	function setFormFieldsToShow() {
		$this->formFieldsToShow = t3lib_div::trimExplode(
			',',
			$this->getConfValueString('feUserFieldsToDisplay', 's_general')
		);

		return;
	}

	/**
	 * Reads the list of required form fields from the configuration and stores
	 * it in $this->requiredFormFields.
	 *
	 * @access	private
	 */
	function setRequiredFormFields() {
		$this->requiredFormFields = t3lib_div::trimExplode(
			',',
			$this->getConfValueString('requiredFeUserFields', 's_general')
		);

		return;
	}

	/**
	 * Gets the path to the HTML template as set in the TS setup or flexforms.
	 * The returned path will always be an absolute path in the file system;
	 * EXT: references will automatically get resolved.
	 *
	 * @return	string		the path to the HTML template as an absolute path in the file system, will not be empty in a correct configuration, will never be null
	 *
	 * @access	public
	 */
	function getTemplatePath() {
		return t3lib_div::getFileAbsFileName(
			$this->getConfValueString('templateFile', 's_template_special',	true)
		);
	}

	/**
	 * Creates the HTML output of the form.
	 *
	 * @return 	string		HTML of the form
	 *
	 * @access	protected
	 */
	function renderForm() {
		$rawForm = $this->form->_render();

		$this->processTemplate($rawForm);
		$this->setLabels();
		$this->hideUnusedFormFields();

		return $this->substituteMarkerArrayCached('', 1);
	}

	/**
	 * Hides form fields that are disabled via TS setup from the templating
	 * process.
	 *
	 * @access	protected
	 */
	function hideUnusedFormFields() {
		static $availableFormFields = array(
			'company',
			'gender',
			'name',
			'first_name',
			'last_name',
			'address',
			'zip',
			'city',
			'country',
			'static_info_country',
			'email',
			'telephone',
			'fax',
			'date_of_birth',
			'status'
		);

		$formFieldsToHide = array_diff(
			$availableFormFields,
			$this->formFieldsToShow
		);

		$this->readSubpartsToHide(
			implode(',', $formFieldsToHide),
			'wrapper'
		);

		return;
	}

	/**
	 * Checks whether a form field should be displayed (and evaluated) at all.
	 * This is specified via TS setup (or flexforms) using the
	 * "feUserFieldsToDisplay" variable.
	 *
	 * @param	array		the contents of the "params" child of the userobj node as key/value pairs (used for retrieving the current form field name)
	 *
	 * @return	boolean		true if the current form field should be displayed, false otherwise
	 *
	 * @access	public
	 */
	function isFormFieldEnabled($parameters) {
		return in_array($parameters['elementname'], $this->formFieldsToShow);
	}

	/**
	 * Provides a localized list of localized country names from static_tables.
	 *
	 * If $parameters['alpha3'] is set, the alpha3 codes will be used as form
	 * values. Otherwise, the localized country names will be used as values.
	 *
	 * @param	array		array that contains any pre-filled data (unused)
	 * @param	array		contents of the "params" XML child of the userobj node (needs to contain an element with the key "key")
	 *
	 * @return	array		a list of localized country names from static_tables as an array with the keys "caption" (for the localized title) and "value" (either the country's alpha3 code or the localized name)
	 *
	 * @access	public
	 */
	function populateListCountries($unused, $parameters) {
		$this->initStaticInfo();
		$allCountries = $this->staticInfo->initCountries();

		$result = array();
		// Add an empty item at the top so we won't have Afghanistan (the first
		// entry) pre-selected for empty values.
		$result[] = array(
			'caption' => ' ',
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
	 * Creates and initializes $this->staticInfo (if that hasn't been done yet).
	 *
	 * @access	private
	 */
	function initStaticInfo() {
		if (!$this->staticInfo) {
			if (is_file(
				t3lib_extMgm::extPath('static_info_tables')
					.'pi1/class.tx_staticinfotables_pi1.php'
			)) {
				$this->staticInfo
					=& t3lib_div::makeInstance('tx_staticinfotables_pi1');
			} else  {
				// Use sr_static_info as a fallback for TYPO3 < 4.x.
				$this->staticInfo =& t3lib_div::makeInstance('tx_srstaticinfo_pi1');
			}

			$this->staticInfo->init();
		}

		return;
	}

	/**
	 * Gets the PID of the system folder in which new FE user records will be
	 * stored.
	 *
	 * @return	integer		the PID of the page where FE-created events will be stored
	 *
	 * @access	public
	 */
	function getPidForNewUserRecords() {
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
	 * @return	string		the URL set as GET parameter (or an empty string if there is no such GET parameter)
	 *
	 * @access	public
	 */
	function getRedirectUrlAndLoginUser() {
		$result = t3lib_div::_GP('redirect_url');

		if (empty($result)) {
			// Redirect to the current page if no redirect URL is provided.
			$result = t3lib_div::locationHeaderUrl(
				$this->cObj->getTypoLink_URL($GLOBALS['TSFE']->id)
			);
		}

		$datahandler =& $this->form->oDataHandler;
		$userName = $datahandler->_getThisFormData('username');
		// The array key "uident" is required by the compareUident function.
		$loginData = array(
			'uident'=> $datahandler->_getThisFormData('password')
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

			$GLOBALS['TSFE']->fe_user->setKey(
				'user',
				$this->extKey,
				'1'
			);
			$GLOBALS['TSFE']->fe_user->storeSessionData();
		}

		return $result;
	}

	/**
	 * Creates a unique FE user name. It consists of the entered e-mail address.
	 * If a user with that user name already exists, a number will be appended.
	 *
	 * @return	string		a user name (will not be empty)
	 *
	 * @access	public
	 */
	function getUserName() {
		$enteredEmail = $this->form->oDataHandler->_getThisFormData('email');
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
	 * @return	string		a random 8 character password
	 */
	function getPassword() {
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
	 * Retrieves the UID of the FE user group for the FE user to create.
	 *
	 * @return	integer		UID of the FE user group for the FE user to create, will not be zero if the plug-in has been configured correctly
	 */
	function getUserGroup() {
		return $this->getConfValueInteger(
			'groupForNewFeUsers',
			's_general'
		);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/onetimeaccount/pi1/class.tx_onetimeaccount_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/onetimeaccount/pi1/class.tx_onetimeaccount_pi1.php']);
}

?>
