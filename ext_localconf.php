<?php

defined('TYPO3_MODE') or die('Access denied.');

(static function (): void {
    $typo3Version = new \TYPO3\CMS\Core\Information\Typo3Version();
    if ($typo3Version->getMajorVersion() >= 10) {
        // This makes the plugin available for front-end rendering.
        \TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
            // extension name, matching the PHP namespace
            'OliverKlee.Onetimeaccount',
            // arbitrary, but unique plugin name (not visible in the BE)
            'WithAutologin',
            // all actions
            [
                \OliverKlee\Onetimeaccount\Controller\UserWithAutologinController::class => 'new',
            ],
            // non-cacheable actions
            [
                \OliverKlee\Onetimeaccount\Controller\UserWithAutologinController::class => 'new',
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
                \OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController::class => 'new',
            ],
            // non-cacheable actions
            [
                \OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController::class => 'new',
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
                'UserWithAutologin' => 'new',
            ],
            // non-cacheable actions
            [
                'UserWithAutologin' => 'new',
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
                'UserWithoutAutologin' => 'new',
            ],
            // non-cacheable actions
            [
                'UserWithoutAutologin' => 'new',
            ]
        );
        // TYPO3 V9
    }
})();
