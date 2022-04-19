<?php

defined('TYPO3_MODE') or die('Access denied.');

(static function (): void {
    // This makes the plugin available for front-end rendering.
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        // extension name, matching the PHP namespaces (but without the vendor)
        'OneTimeAccount',
        // arbitrary, but unique plugin name (not visible in the BE)
        'WithAutologin',
        // all actions
        [
            'UserWithAutologin' => 'new',
        ],
        // non-cacheable actions
        [
            'UserWithAutologin' => 'new',
        ]
    );
    // This makes the plugin available for front-end rendering.
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
        // extension name, matching the PHP namespaces (but without the vendor)
        'OneTimeAccount',
        // arbitrary, but unique plugin name (not visible in the BE)
        'WithoutAutologin',
        // all actions
        [
            'UserWithoutAutologin' => 'new',
        ],
        // non-cacheable actions
        [
            'UserWithoutAutologin' => 'new',
        ]
    );
})();
