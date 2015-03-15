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

$EM_CONF[$_EXTKEY] = array(
	'title' => 'One-time FE account',
	'description' => 'This extension allows users to create a one-time FE account to which they will be automatically logged in (without having to enter a user name or password). This extension also supports saltedpasswords and rsaauth.',
	'category' => 'plugin',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'shy' => '',
	'dependencies' => 'felogin,static_info_tables,oelib,ameos_formidable',
	'conflicts' => 'kb_md5fepw',
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
	'version' => '1.0.50',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.0-5.6.99',
			'typo3' => '4.5.0-6.2.99',
			'ameos_formidable' => '1.1.563-1.9.99',
			'oelib' => '0.8.0-1.0.99',
			'static_info_tables' => '2.1.0-6.2.99',
		),
		'conflicts' => array(
			'kb_md5fepw' => '0.0.0-',
		),
		'suggests' => array(
			'sr_feuser_register' => '2.2.0-',
		),
	),
	'_md5_values_when_last_written' => '',
	'suggests' => array(
	),
);
