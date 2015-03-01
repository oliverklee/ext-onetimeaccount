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
 * This class provides functions for filling the flex-forms.
 *
 * @package TYPO3
 * @subpackage tx_onetimeaccount
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_OneTimeAccount_BackEnd_FlexForms {
	/**
	 * @var string[]
	 */
	protected $fieldsForRequiring = array(
		'company', 'title', 'name', 'first_name', 'last_name', 'address', 'zip', 'city', 'country', 'email', 'www',
		'telephone', 'fax', 'gender', 'static_info_country', 'date_of_birth', 'status', 'comments',
	);

	/**
	 * @var string[]
	 */
	protected $fieldsFromSystemExtensions = array(
		'company', 'title', 'name', 'first_name', 'last_name', 'address', 'zip', 'city', 'country', 'email', 'www',
		'telephone', 'fax', 'usergroup',
	);

	/**
	 * @var string[]
	 */
	protected $fieldsFromSrFrontEndUserRegister = array(
		'gender', 'zone', 'static_info_country', 'date_of_birth', 'status', 'comments',
	);

	/**
	 * @var string[]
	 */
	protected $fieldsFromSfRegister = array(
		'gender', 'zone', 'static_info_country', 'date_of_birth', 'status', 'comments',
	);

	/**
	 * @var string[]
	 */
	protected $fieldsFromDirectMail = array(
		'module_sys_dmail_newsletter', 'module_sys_dmail_html'
	);

	/**
	 * @var string[]
	 */
	protected $languageLabels = array();

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->loadLanguageLabels();
	}

	/**
	 * Returns the selectable items for the fields to display.
	 *
	 * @param string[][][] $configuration
	 *
	 * @return string[][][]
	 */
	public function getFieldsToDisplay(array $configuration) {
		return $this->createCheckboxFieldsForKeys($configuration, $this->getAvailableFieldNames());
	}

	/**
	 * Returns the selectable items for the fields to require.
	 *
	 * @param string[][][] $configuration
	 *
	 * @return string[][][]
	 */
	public function getFieldsToRequire(array $configuration) {
		$availableFields = array_intersect($this->getAvailableFieldNames(), $this->fieldsForRequiring);
		return $this->createCheckboxFieldsForKeys($configuration, $availableFields);
	}

	/**
	 * Returns the selectable items for $availableFields.
	 *
	 * @param string[][][] $configuration
	 * @param string[] $availableFields
	 *
	 * @return string[][][]
	 */
	protected function createCheckboxFieldsForKeys(array $configuration, array $availableFields) {
		/** @var string[][] $result */
		$items = array();
		foreach ($availableFields as $fieldName) {
			$label = $this->getLanguageLabelForFrontEndUserField($fieldName);
			$items[] = array($label, $fieldName);
		}

		$configuration['items'] = $items;

		return $configuration;
	}

	/**
	 * Returns the names of all relevant FE user fields that are available through installed extensions.
	 *
	 * @return string[]
	 */
	protected function getAvailableFieldNames() {
		$availableFieldNames = $this->fieldsFromSystemExtensions;
		if (t3lib_extMgm::isLoaded('sr_feuser_register')) {
			$availableFieldNames = array_merge($availableFieldNames, $this->fieldsFromSrFrontEndUserRegister);
		}
		if (t3lib_extMgm::isLoaded('sf_register')) {
			$availableFieldNames = array_merge($availableFieldNames, $this->fieldsFromSfRegister);
		}
		if (t3lib_extMgm::isLoaded('direct_mail')) {
			$availableFieldNames = array_merge($availableFieldNames, $this->fieldsFromDirectMail);
		}

		return array_unique($availableFieldNames);
	}

	/**
	 * Reads the language labels into $this->languageLabels (if they have not been loaded yet).
	 *
	 * @return void
	 */
	protected function loadLanguageLabels() {
		if (!empty($this->languageLabels)) {
			return;
		}

		$languageFilePath = t3lib_extMgm::extPath('onetimeaccount') . 'locallang_db.xml';
		if (class_exists('t3lib_l10n_parser_Llxml')) {
			/** @var $xmlParser t3lib_l10n_parser_Llxml */
			$xmlParser = t3lib_div::makeInstance('t3lib_l10n_parser_Llxml');
			$this->languageLabels = $xmlParser->getParsedData($languageFilePath, $this->getLanguageService()->lang);
		} else {
			$this->languageLabels = t3lib_div::readLLXMLfile($languageFilePath, $this->getLanguageService()->lang);
		}
	}

	/**
	 * Finds the language label for $fieldName.
	 *
	 * @param string $fieldName the field name, e.g. "full_name"
	 *
	 * @return string
	 */
	protected function getLanguageLabelForFrontEndUserField($fieldName) {
		$fullKey = 'fe_users.' . $fieldName;
		return $this->getLanguageService()->getLLL($fullKey, $this->languageLabels);
	}

	/**
	 * Returns the language service.
	 *
	 * @return language
	 */
	protected function getLanguageService() {
		return $GLOBALS['LANG'];
	}
}