<?php
if (!defined('TYPO3_MODE')) {
	die('Access denied.');
}

// extends TypoScript from static template uid=43 to set up userdefined tag
t3lib_extMgm::addPItoST43(
	$_EXTKEY,
	'pi1/class.tx_onetimeaccount_pi1.php',
	'_pi1',
	'list_type',
	0
);

$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include'][$_EXTKEY]
	= 'EXT:' . $_EXTKEY . '/class.tx_onetimeaccount_eid.php';
?>