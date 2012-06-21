<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009-2012 Dmitry Dulepov <dmitry@typo3.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/

require_once(t3lib_extMgm::extPath('oelib') . 'class.tx_oelib_Autoloader.php');

/**
 * This class contains a user login script which uses the login data encoded
 * in GET['data'].
 *
 * In addition, key "onetimeaccount" with the value "1" will be written to the
 * front-end user session.
 *
 * Note: This class is not unit-tested because is does some low-lewel stuff
 * that doesn't allow unit-testing.
 *
 * @author Dmitry Dulepov <dmitry@typo3.org>
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 *
 * @package TYPO3
 * @subpackage tx_onetimeaccount
 */
class tx_onetimeaccount_eid {
	/**
	 * Logs in the user and sends the user to the destination URL by setting
	 * the appropriate redirection headers.
	 *
	 * The user credentials are supplied via a JSON-encoded array that comes as a GET parameter.
	 *
	 * @return void
	 */
	public function loginAndRedirect() {
		tslib_eidtools::connectDB();

		$postData = json_decode(base64_decode($GLOBALS['_GET']['data']), TRUE);
		if (!is_array($postData) || empty($postData)) {
			$this->log('POST data is no array or empty.', 3);
			return;
		}

		$url = $postData['url'];
		if (!preg_match('/^https?:\/\//', $url)) {
			$this->log('URL has no http(s) prefix: ' . $url, 3);
			return;
		}

		$GLOBALS['_POST']['user'] = $postData['user'];
		$GLOBALS['_POST']['pass'] = $postData['pass'];
		if (isset($postData['challenge'])) {
			$GLOBALS['_POST']['challenge'] = $postData['challenge'];
		}
		$GLOBALS['_POST']['pid'] = $postData['pid'];
		$GLOBALS['_POST']['logintype'] = 'login';

		$this->log(
			'Logging in user "' . $postData['user'] . '" in sysfolder ' .
			$postData['pid'] .
			(isset($postData['challenge']) ? ' with challenge' : '') .
			'.'
		);
		$frontEndUser = tslib_eidtools::initFeUser();
		$frontEndUser->setKey('user', 'onetimeaccount', TRUE);
		$frontEndUser->storeSessionData();

		$this->log('Redirecting after login to: ' . $url);
		header('HTTP/1.0 302 Redirect');
		header('Location: ' . $url);
	}

	/**
	 * Logs $message to the TYPO3 development log if logging is enabled for
	 * this extension.
	 *
	 * @param string $message the message to log, must not be empty
	 * @param integer $severity
	 *        0 = info, 1 = notice, 2 = warning, 3 = fatal error, -1 = OK
	 *
	 * @return void
	 */
	private function log($message, $severity = 0) {
		if (!tx_oelib_configurationProxy::getInstance('onetimeaccount')->getAsBoolean('enableLogging')) {
			return;
		}

		t3lib_div::devLog($message, 'onetimeaccount', $severity);
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/onetimeaccount/class.tx_onetimeaccount_eid.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/onetimeaccount/class.tx_onetimeaccount_eid.php']);
}

$instance = t3lib_div::makeInstance('tx_onetimeaccount_eid');
/**
 * @var $instance tx_onetimeaccount_eid
 */
$instance->loginAndRedirect();
?>