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
        'EXT:onetimeaccount/ext_icon.svg',
    ],
    'list_type'
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'onetimeaccount',
    'Configuration/TypoScript/',
    'One-time FE account creator'
);

if (TYPO3_MODE === 'BE') {
    /** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
    $iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $iconRegistry->registerIcon(
        'ext-onetimeaccount-wizard-icon',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:onetimeaccount/ext_icon.svg']
    );
}
