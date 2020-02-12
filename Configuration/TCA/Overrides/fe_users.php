<?php

defined('TYPO3_MODE') or die('Access denied.');

$GLOBALS['TCA']['fe_users']['columns']['company']['config'] = [
    'type' => 'text',
    'cols' => '20',
    'rows' => '3',
];
