<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'One-time FE account',
    'description' => 'Allow users to create a one-time FE account to which they will be automatically logged in (without having to enter a user name or password).',
    'version' => '5.1.1',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-8.1.99',
            'typo3' => '10.4.11-10.4.99',
            'extbase' => '10.4.11-10.4.99',
            'fluid' => '10.4.11-10.4.99',
            'feuserextrafields' => '4.2.1-5.99.99',
            'oelib' => '4.3.0-5.99.99',
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
