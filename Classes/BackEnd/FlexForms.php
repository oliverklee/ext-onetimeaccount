<?php
namespace OliverKlee\Onetimeaccount\BackEnd;

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

use TYPO3\CMS\Core\Localization\Parser\LocallangXmlParser;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * This class provides functions for filling the FlexForms.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FlexForms
{
    /**
     * @var string[]
     */
    protected static $fieldsForRequiring = [
        'company', 'title', 'name', 'first_name', 'last_name', 'address', 'zip', 'city', 'country', 'email', 'www',
        'telephone', 'fax', 'gender', 'static_info_country', 'date_of_birth', 'status', 'comments',
    ];

    /**
     * @var string[]
     */
    protected static $fieldsFromSystemExtensions = [
        'company', 'title', 'name', 'first_name', 'last_name', 'address', 'zip', 'city', 'country', 'email', 'www',
        'telephone', 'fax', 'usergroup',
    ];

    /**
     * @var string[]
     */
    protected static $fieldsFromSrFrontEndUserRegister = [
        'gender', 'zone', 'static_info_country', 'date_of_birth', 'status', 'comments',
    ];

    /**
     * @var string[]
     */
    protected static $fieldsFromSfRegister = ['gender', 'zone', 'static_info_country', 'date_of_birth', 'status', 'comments'];

    /**
     * @var string[]
     */
    protected static $fieldsFromDirectMail = ['module_sys_dmail_newsletter', 'module_sys_dmail_html'];

    /**
     * @var string[]
     */
    protected $languageLabels = [];

    /**
     * Constructor.
     *
     * @throws \InvalidArgumentException
     * @throws \BadFunctionCallException
     */
    public function __construct()
    {
        $this->loadLanguageLabels();
    }

    /**
     * Returns the selectable items for the fields to display.
     *
     * @param string[][][] $configuration
     *
     * @return string[][][]
     *
     * @throws \BadFunctionCallException
     */
    public function getFieldsToDisplay(array $configuration)
    {
        return $this->createCheckboxFieldsForKeys($configuration, $this->getAvailableFieldNames());
    }

    /**
     * Returns the selectable items for the fields to require.
     *
     * @param string[][][] $configuration
     *
     * @return string[][][]
     *
     * @throws \BadFunctionCallException
     */
    public function getFieldsToRequire(array $configuration)
    {
        $availableFields = array_intersect($this->getAvailableFieldNames(), static::$fieldsForRequiring);
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
    protected function createCheckboxFieldsForKeys(array $configuration, array $availableFields)
    {
        /** @var string[][] $items */
        $items = [];
        foreach ($availableFields as $fieldName) {
            $label = $this->getLanguageLabelForFrontEndUserField($fieldName);
            $items[] = [$label, $fieldName];
        }

        $configuration['items'] = $items;

        return $configuration;
    }

    /**
     * Returns the names of all relevant FE user fields that are available through installed extensions.
     *
     * @return string[]
     *
     * @throws \BadFunctionCallException
     */
    protected function getAvailableFieldNames()
    {
        $availableFieldNames = static::$fieldsFromSystemExtensions;
        if (ExtensionManagementUtility::isLoaded('sr_feuser_register')) {
            $availableFieldNames = array_merge($availableFieldNames, static::$fieldsFromSrFrontEndUserRegister);
        }
        if (ExtensionManagementUtility::isLoaded('sf_register')) {
            $availableFieldNames = array_merge($availableFieldNames, static::$fieldsFromSfRegister);
        }
        if (ExtensionManagementUtility::isLoaded('direct_mail')) {
            $availableFieldNames = array_merge($availableFieldNames, static::$fieldsFromDirectMail);
        }

        return array_unique($availableFieldNames);
    }

    /**
     * Reads the language labels into $this->languageLabels (if they have not been loaded yet).
     *
     * @return void
     *
     * @throws \BadFunctionCallException
     * @throws \InvalidArgumentException
     */
    protected function loadLanguageLabels()
    {
        if (count($this->languageLabels) > 0) {
            return;
        }

        $languageFilePath = ExtensionManagementUtility::extPath('onetimeaccount') . 'locallang_db.xml';
        /** @var LocallangXmlParser $xmlParser */
        $xmlParser = GeneralUtility::makeInstance(LocallangXmlParser::class);
        $this->languageLabels = $xmlParser->getParsedData($languageFilePath, $this->getLanguageService()->lang);
    }

    /**
     * Finds the language label for $fieldName.
     *
     * @param string $fieldName the field name, e.g. "full_name"
     *
     * @return string
     */
    protected function getLanguageLabelForFrontEndUserField($fieldName)
    {
        $fullKey = 'fe_users.' . $fieldName;

        return $this->getLanguageService()->getLLL($fullKey, $this->languageLabels);
    }

    /**
     * Returns the language service.
     *
     * @return LanguageService
     */
    protected function getLanguageService()
    {
        return $GLOBALS['LANG'];
    }
}
