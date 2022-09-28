<?php

defined('TYPO3_MODE') or die('Access denied.');

(static function (): void {
    $typo3Version = new \TYPO3\CMS\Core\Information\Typo3Version();
    if ($typo3Version->getMajorVersion() >= 10) {
        // This makes the plugin available for front-end rendering.
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            // extension name, matching the PHP namespaces (but without the vendor)
            'Onetimeaccount',
            // arbitrary, but unique plugin name (not visible in the BE)
            'WithAutologin',
            // all actions
            [
                \OliverKlee\Onetimeaccount\Controller\UserWithAutologinController::class => 'new, create',
            ],
            // non-cacheable actions
            [
                \OliverKlee\Onetimeaccount\Controller\UserWithAutologinController::class => 'new, create',
            ]
        );
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
    } else {
        // This makes the plugin available for front-end rendering.
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            // extension name, matching the PHP namespace
            'OliverKlee.Onetimeaccount',
            // arbitrary, but unique plugin name (not visible in the BE)
            'WithAutologin',
            // all actions
            [
                'UserWithAutologin' => 'new, create',
            ],
            // non-cacheable actions
            [
                'UserWithAutologin' => 'new, create',
            ]
        );
        // This makes the plugin available for front-end rendering.
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            // extension name, matching the PHP namespace
            'OliverKlee.Onetimeaccount',
            // arbitrary, but unique plugin name (not visible in the BE)
            'WithoutAutologin',
            // all actions
            [
                'UserWithoutAutologin' => 'new, create',
            ],
            // non-cacheable actions
            [
                'UserWithoutAutologin' => 'new, create',
            ]
        );
        // TYPO3 V9
    }
})();
