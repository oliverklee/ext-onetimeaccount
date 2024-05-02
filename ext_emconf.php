<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'One-time FE account',
    'description' => 'Allow users to create a one-time FE account to which they will be automatically logged in (without having to enter a user name or password).',
    'version' => '6.4.0',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'php' => '7.4.0-8.3.99',
            'typo3' => '11.5.17-11.5.99',
            'extbase' => '11.5.17-11.5.99',
            'fluid' => '11.5.17-11.5.99',
            'feuserextrafields' => '6.2.0-6.99.99',
            'oelib' => '5.1.0-6.99.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
            'seminars' => '',
        ],
    ],
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'author' => 'Oliver Klee',
    'author_email' => 'typo3-coding@oliverklee.de',
    'author_company' => 'oliverklee.de',
    'autoload' => [
        'psr-4' => [
            'OliverKlee\\Onetimeaccount\\' => 'Classes/',
        ],
    ],
    'autoload-dev' => [
        'psr-4' => [
            'OliverKlee\\Onetimeaccount\\Tests\\' => 'Tests/',
        ],
    ],
];
