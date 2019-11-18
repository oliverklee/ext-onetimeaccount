<?php
defined('TYPO3_MODE') or die('Access denied.');

/** @var \TYPO3\CMS\Core\Imaging\IconRegistry $iconRegistry */
$iconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
$iconRegistry->registerIcon(
    'ext-onetimeaccount-wizard-icon',
    \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
    ['source' => 'EXT:onetimeaccount/Resources/Public/Icons/Extension.svg']
);

// extends TypoScript from static template uid=43 to set up user-defined tag
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPItoST43(
    'onetimeaccount',
    'pi1/class.tx_onetimeaccount_pi1.php',
    '_pi1',
    'list_type',
    0
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('
    <INCLUDE_TYPOSCRIPT: source="FILE:EXT:onetimeaccount/Configuration/TSconfig/ContentElementWizard.txt">
');
