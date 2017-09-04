<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "onetimeaccount".
 *
 * Auto generated 17-01-2015 17:41
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = [
    'title' => 'One-time FE account',
    'description' => 'This extension allows users to create a one-time FE account to which they will be automatically logged in (without having to enter a user name or password). This extension also supports saltedpasswords and rsaauth.',
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
    'version' => '2.0.1',
    'constraints' => [
        'depends' => [
            'php' => '5.5.0-7.1.99',
            'typo3' => '6.2.0-7.9.99',
            'mkforms' => '2.0.0-3.99.99',
            'oelib' => '1.0.0-1.99.99',
            'static_info_tables' => '6.2.0-',
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
        'classmap' => ['Classes', 'pi1', 'Tests'],
    ],
];
