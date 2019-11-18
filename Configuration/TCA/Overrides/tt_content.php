<?php
defined('TYPO3_MODE') or die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:onetimeaccount/Resources/Private/Language/locallang_db.xlf:tt_content.list_type_pi1',
        'onetimeaccount_pi1',
        'EXT:onetimeaccount/Resources/Public/Icons/Extension.svg',
    ],
    'list_type',
    'onetimeaccount'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['onetimeaccount_pi1']
    = 'layout,select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['onetimeaccount_pi1'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'onetimeaccount_pi1',
    'FILE:EXT:onetimeaccount/Configuration/FlexForms/FlexForms.xml'
);
