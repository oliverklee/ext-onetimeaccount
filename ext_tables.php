<?php
defined('TYPO3_MODE') or die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'onetimeaccount_pi1',
    'FILE:EXT:onetimeaccount/Configuration/FlexForms/FlexForms.xml'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPlugin(
    [
        'LLL:EXT:onetimeaccount/Resources/Private/Language/locallang_db.xlf:tt_content.list_type_pi1',
        'onetimeaccount_pi1',
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('onetimeaccount') . 'ext_icon.gif',
    ],
    'list_type'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'onetimeaccount',
    'Configuration/TypoScript/',
    'One-time FE account creator'
);

if (TYPO3_MODE === 'BE') {
    $TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses']['tx_onetimeaccount_pi1_wizicon']
        = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('onetimeaccount')
            . 'pi1/class.tx_onetimeaccount_pi1_wizicon.php';
}
