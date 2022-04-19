<?php

defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile('onetimeaccount', 'Configuration/TypoScript', 'Onetimeaccount');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'onetimeaccount',
    'Configuration/TypoScript/Frontend/',
    'Onetimeaccount frontend (optional)'
);
