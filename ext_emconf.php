<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'One-time FE account',
    'description' => 'Allow users to create a one-time FE account to which they will be automatically logged in (without having to enter a user name or password).',
    'version' => '5.0.0',
    'category' => 'plugin',
    'constraints' => [
        'depends' => [
            'php' => '7.2.0-8.1.99',
            'typo3' => '9.5.16-10.4.99',
            'extbase' => '9.5.0-10.4.99',
            'fluid' => '9.5.0-10.4.99',
            'feuserextrafields' => '3.0.0-4.99.99',
            'oelib' => '4.1.6-4.99.99',
        ],
        'conflicts' => [
        ],
        'suggests' => [
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
