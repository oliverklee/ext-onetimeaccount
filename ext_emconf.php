<?php

########################################################################
# Extension Manager/Repository config file for ext: "onetimeaccount"
#
# Auto generated 03-06-2007 15:50
#
# Manual updates:
# Only the data in the array - anything else is removed by next write.
# "version" and "dependencies" must not be touched!
########################################################################

$EM_CONF[$_EXTKEY] = array(
	'title' => 'One-time FE account',
	'description' => 'This extension allows users to create a one-time FE account to which they will be automatically logged in. The users don\'t need to enter a user name or password. So this login can be used only one time.',
	'category' => 'plugin',
	'author' => 'Oliver Klee',
	'author_email' => 'typo3-coding@oliverklee.de',
	'shy' => '',
	'dependencies' => 'ameos_formidable,oelib,static_info_tables',
	'conflicts' => '',
	'priority' => '',
	'module' => '',
	'state' => 'alpha',
	'internal' => '',
	'uploadfolder' => 1,
	'createDirs' => '',
	'modify_tables' => '',
	'clearCacheOnLoad' => 0,
	'lockType' => '',
	'author_company' => '',
	'version' => '0.0.0',
	'constraints' => array(
		'depends' => array(
			'php' => '4.1.0-0.0.0',
			'typo3' => '3.8.0-0.0.0',
			'ameos_formidable' => '0.7.0-',
			'oelib' => '0.3.0-',
			'static_info_tables' => '2.0.0-',
		),
		'conflicts' => array(
		),
		'suggests' => array(
			'sr_feuser_register' => '2.2.0-',
		),
	),
	'_md5_values_when_last_written' => 'a:19:{s:9:"ChangeLog";s:4:"b0f5";s:39:"class.tx_onetimeaccount_configcheck.php";s:4:"6d1f";s:21:"ext_conf_template.txt";s:4:"1c02";s:12:"ext_icon.gif";s:4:"1bdc";s:17:"ext_localconf.php";s:4:"00f9";s:14:"ext_tables.php";s:4:"0660";s:13:"locallang.xml";s:4:"ada6";s:16:"locallang_db.xml";s:4:"02a3";s:14:"doc/manual.sxw";s:4:"372a";s:14:"pi1/ce_wiz.gif";s:4:"02b6";s:35:"pi1/class.tx_onetimeaccount_pi1.php";s:4:"fce6";s:43:"pi1/class.tx_onetimeaccount_pi1_wizicon.php";s:4:"ccd3";s:13:"pi1/clear.gif";s:4:"cc11";s:21:"pi1/flexforms_pi1.xml";s:4:"e7ce";s:17:"pi1/locallang.xml";s:4:"70ef";s:26:"pi1/onetimeaccount_pi1.css";s:4:"d41d";s:27:"pi1/onetimeaccount_pi1.html";s:4:"a03d";s:24:"pi1/static/editorcfg.txt";s:4:"0d1c";s:20:"pi1/static/setup.txt";s:4:"95ca";}',
	'suggests' => array(
	),
);

?>