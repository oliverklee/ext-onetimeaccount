<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'One-time FE account',
    'description' => 'Allow users to create a one-time FE account to which they will be automatically logged in (without having to enter a user name or password).',
    'category' => 'plugin',
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'shy' => '',
    'dependencies' => 'felogin,static_info_tables,oelib,mkforms',
    'conflicts' => '',
    'priority' => '',
    'module' => '',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => 0,
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => 0,
    'lockType' => '',
    'author_company' => 'oliverklee.de',
    'version' => '2.1.0',
    'constraints' => [
        'depends' => [
            'php' => '5.6.0-7.2.99',
            'typo3' => '7.6.0-8.7.99',
            'mkforms' => '3.0.14-3.99.99',
            'oelib' => '2.0.0-2.99.99',
            'static_info_tables' => '6.4.0-',
        ],
        'conflicts' => [
            'kb_md5fepw' => '0.0.0-',
        ],
        'suggests' => [
            'sr_feuser_register' => '2.2.0-',
        ],
    ],
    '_md5_values_when_last_written' => '',
    'autoload' => [
        'classmap' => [
            'Classes',
            'pi1',
            'Tests',
        ],
    ],
];
