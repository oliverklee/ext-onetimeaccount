<?php

defined('TYPO3_MODE') || die();

// This makes the plugin selectable in the BE.
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    // extension name, matching the PHP namespace
    'OliverKlee.Onetimeaccount',
    // arbitrary, but unique plugin name (not visible in the BE)
    'WithAutologin',
    // plugin title, as visible in the drop-down in the BE
    'LLL:EXT:onetimeaccount/Resources/Private/Language/locallang.xlf:plugin.withAutologin',
    // the icon visible in the drop-down in the BE
    'EXT:onetimeaccount/Resources/Public/Icons/Extension.svg'
);

// This removes the default controls from the plugin.
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['onetimeaccount_withautologin']
    = 'recursive,select_key,pages';
// These two commands add the flexform configuration for the plugin.
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['onetimeaccount_withautologin'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'onetimeaccount_withautologin',
    'FILE:EXT:onetimeaccount/Configuration/FlexForms/Plugin.xml'
);

// This makes the plugin selectable in the BE.
\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    // extension name, matching the PHP namespace
    'OliverKlee.Onetimeaccount',
    // arbitrary, but unique plugin name (not visible in the BE)
    'WithoutAutologin',
    // plugin title, as visible in the drop-down in the BE
    'LLL:EXT:onetimeaccount/Resources/Private/Language/locallang.xlf:plugin.withoutAutologin',
    // the icon visible in the drop-down in the BE
    'EXT:onetimeaccount/Resources/Public/Icons/Extension.svg'
);

// This removes the default controls from the plugin.
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['onetimeaccount_withoutautologin']
    = 'recursive,select_key,pages';
// These two commands add the flexform configuration for the plugin.
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['onetimeaccount_withoutautologin'] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    'onetimeaccount_withoutautologin',
    'FILE:EXT:onetimeaccount/Configuration/FlexForms/Plugin.xml'
);
