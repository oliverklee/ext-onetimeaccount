<?php

defined('TYPO3') or die('Access denied.');

(static function (): void {
    // This makes the plugin available for front-end rendering.
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    // extension name, matching the PHP namespaces (but without the vendor)
        'Onetimeaccount',
        // arbitrary, but unique plugin name (not visible in the BE)
        'WithoutAutologin',
        // all actions
        [
            \OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController::class => 'new, create',
        ],
        // non-cacheable actions
        [
            \OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController::class => 'new, create',
        ]
    );
})();
