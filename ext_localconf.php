<?php
defined('TYPO3_MODE') or die('Access denied.');

// extends TypoScript from static template uid=43 to set up userdefined tag
t3lib_extMgm::addPItoST43(
	$_EXTKEY,
	'pi1/class.tx_onetimeaccount_pi1.php',
	'_pi1',
	'list_type',
	0
);