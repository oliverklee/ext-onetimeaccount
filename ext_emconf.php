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
	'dependencies' => 'ameos_formidable,oelib,static_info_tables',
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
	'version' => '0.9.52',
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
	'_md5_values_when_last_written' => 'a:49:{s:9:"ChangeLog";s:4:"9d79";s:39:"class.tx_onetimeaccount_configcheck.php";s:4:"7f33";s:16:"ext_autoload.php";s:4:"c6f5";s:21:"ext_conf_template.txt";s:4:"b12b";s:12:"ext_icon.gif";s:4:"27c7";s:17:"ext_localconf.php";s:4:"ab5c";s:14:"ext_tables.php";s:4:"b2ea";s:13:"locallang.xml";s:4:"d2a5";s:16:"locallang_db.xml";s:4:"b6fc";s:23:"Documentation/Index.rst";s:4:"f56a";s:35:"Documentation/Development/Index.rst";s:4:"d66d";s:69:"Documentation/Development/ChangingOrCustomizingTheExtension/Index.rst";s:4:"3c14";s:79:"Documentation/Development/DevelopmentWorkflowAndCodingStyleGuidelines/Index.rst";s:4:"c7e0";s:62:"Documentation/Development/GettingTheExtensionFromGit/Index.rst";s:4:"e7e8";s:36:"Documentation/Introduction/Index.rst";s:4:"d6ed";s:48:"Documentation/Introduction/KeyFeatures/Index.rst";s:4:"74b3";s:49:"Documentation/Introduction/UnderTheHood/Index.rst";s:4:"0d5c";s:49:"Documentation/Introduction/WhatDoesItDo/Index.rst";s:4:"d4bb";s:51:"Documentation/KnownProblemsAndLimitations/Index.rst";s:4:"3e70";s:65:"Documentation/KnownProblemsAndLimitations/ReportingBugs/Index.rst";s:4:"1777";s:33:"Documentation/Reference/Index.rst";s:4:"e675";s:92:"Documentation/Reference/ConstantsForTheFront-endPlug-inInPlugintxOnetimeaccountPi1/Index.rst";s:4:"6dbd";s:88:"Documentation/Reference/SetupForTheFront-endPlug-inInPlugintxOnetimeaccountPi1/Index.rst";s:4:"5c77";s:35:"Documentation/Screenshots/Index.rst";s:4:"29b8";s:43:"Documentation/Screenshots/Credits/Index.rst";s:4:"bdbc";s:48:"Documentation/Screenshots/LiveExamples/Index.rst";s:4:"c2f8";s:47:"Documentation/Screenshots/Sponsorship/Index.rst";s:4:"30ed";s:51:"Documentation/SupportAndKeepingUp-to-date/Index.rst";s:4:"aa24";s:69:"Documentation/SupportAndKeepingUp-to-date/FreePublicSupport/Index.rst";s:4:"e9a6";s:82:"Documentation/SupportAndKeepingUp-to-date/PaidServicesOfferedByTheAuthor/Index.rst";s:4:"5376";s:29:"Documentation/Usage/Index.rst";s:4:"e1e5";s:63:"Documentation/Usage/AddingFieldsToTheRegistrationForm/Index.rst";s:4:"53a9";s:85:"Documentation/Usage/ChangingAddressStreetFieldsFromTextareaToTextInputField/Index.rst";s:4:"f722";s:77:"Documentation/Usage/ChangingTheCompanyFieldFromInputFieldToTextarea/Index.rst";s:4:"8f2c";s:50:"Documentation/Usage/CooluriConfiguration/Index.rst";s:4:"075d";s:52:"Documentation/Usage/InstallingTheExtension/Index.rst";s:4:"31c6";s:40:"Documentation/YourHelpIsWanted/Index.rst";s:4:"a0c6";s:53:"ExampleExtensions/T3X_marijo-0_1_0-z-200909252202.t3x";s:4:"dad8";s:17:"Tests/pi1Test.php";s:4:"6e4e";s:26:"Tests/Fixtures/FakePi1.php";s:4:"294e";s:14:"pi1/ce_wiz.gif";s:4:"be1b";s:35:"pi1/class.tx_onetimeaccount_pi1.php";s:4:"c07b";s:43:"pi1/class.tx_onetimeaccount_pi1_wizicon.php";s:4:"d509";s:21:"pi1/flexforms_pi1.xml";s:4:"460e";s:17:"pi1/locallang.xml";s:4:"6830";s:26:"pi1/onetimeaccount_pi1.css";s:4:"7fbe";s:27:"pi1/onetimeaccount_pi1.html";s:4:"8ee0";s:24:"pi1/static/constants.txt";s:4:"331d";s:20:"pi1/static/setup.txt";s:4:"cf8a";}',
	'suggests' => array(
	),
);
