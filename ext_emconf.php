<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'One-time FE account',
    'description' => 'Allow users to create a one-time FE account to which they will be automatically logged in (without having to enter a user name or password).',
    'version' => '3.0.2',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'php' => '7.0.0-7.2.99',
            'typo3' => '8.7.0-8.7.99',
            'felogin' => '8.7.0-8.7.99',
            'mkforms' => '3.0.21-9.5.99',
            'oelib' => '3.0.0-3.99.99',
            'static_info_tables' => '6.7.5-',
        ],
        'conflicts' => [
            'kb_md5fepw' => '0.0.0-',
        ],
        'suggests' => [
            'femanager' => '5.1.0-',
            'sr_feuser_register' => '5.1.0-',
        ],
    ],
    'state' => 'stable',
    'uploadfolder' => false,
    'clearCacheOnLoad' => false,
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
    'autoload' => [
        'classmap' => [
            'Classes',
            'pi1',
        ],
    ],
    'autoload-dev' => [
        'psr-4' => [
            'OliverKlee\\OneTimeAccount\\Tests\\' => 'Tests/'
        ],
    ],
];
