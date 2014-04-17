<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "onetimeaccount".
 *
 * Auto generated 17-04-2014 16:26
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
	'dependencies' => 'ameos_formidable,oelib,static_info_tables',
	'conflicts' => 'kb_md5fepw',
	'priority' => '',
	'module' => '',
	'state' => 'beta',
	'internal' => '',
	'uploadfolder' => 0,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => 'oliverklee.de',
	'version' => '0.9.51',
	'constraints' => array(
		'depends' => array(
			'php' => '5.3.0-5.5.99',
			'typo3' => '4.5.0-6.1.99',
			'ameos_formidable' => '1.1.563-1.9.99',
			'oelib' => '0.7.0-1.0.99',
			'static_info_tables' => '2.1.0-6.1.99',
		),
		'conflicts' => array(
			'kb_md5fepw' => '0.0.0-',
		),
		'suggests' => array(
			'sr_feuser_register' => '2.2.0-',
		),
	),
	'_md5_values_when_last_written' => 'a:22:{s:9:"ChangeLog";s:4:"d492";s:39:"class.tx_onetimeaccount_configcheck.php";s:4:"0dcd";s:16:"ext_autoload.php";s:4:"c6f5";s:21:"ext_conf_template.txt";s:4:"b12b";s:12:"ext_icon.gif";s:4:"27c7";s:17:"ext_localconf.php";s:4:"239d";s:14:"ext_tables.php";s:4:"0232";s:13:"locallang.xml";s:4:"d2a5";s:16:"locallang_db.xml";s:4:"b6fc";s:17:"Tests/pi1Test.php";s:4:"fe6b";s:26:"Tests/Fixtures/FakePi1.php";s:4:"3c63";s:14:"doc/manual.sxw";s:4:"d9fb";s:39:"doc/T3X_marijo-0_1_0-z-200909252202.t3x";s:4:"dad8";s:14:"pi1/ce_wiz.gif";s:4:"be1b";s:35:"pi1/class.tx_onetimeaccount_pi1.php";s:4:"cbc3";s:43:"pi1/class.tx_onetimeaccount_pi1_wizicon.php";s:4:"81f2";s:21:"pi1/flexforms_pi1.xml";s:4:"460e";s:17:"pi1/locallang.xml";s:4:"6830";s:26:"pi1/onetimeaccount_pi1.css";s:4:"7fbe";s:27:"pi1/onetimeaccount_pi1.html";s:4:"8ee0";s:24:"pi1/static/constants.txt";s:4:"331d";s:20:"pi1/static/setup.txt";s:4:"cf8a";}',
	'suggests' => array(
	),
);
