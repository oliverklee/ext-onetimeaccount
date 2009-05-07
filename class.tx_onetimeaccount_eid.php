<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Dmitry Dulepov <dmitry@typo3.org>
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
	 * The user credentials are supplied via a serialized array that comes as
	 * a GET parameter.
	 */
	public function loginAndRedirect() {
		$postData = @unserialize(
			base64_decode($_GET['data'])
		);
		if (!is_array($postData) || empty($postData)) {
			return;
		}
		if (!preg_match('/^https?:\/\//', $postData['url'])) {
			return;
		}

		$_POST['user'] = $postData['user'];
		$_POST['pass'] = $postData['pass'];
		if (isset($postData['challenge'])) {
			$_POST['challenge'] = $postData['challenge'];
		}
		$_POST['pid'] = $postData['pid'];
		$_POST['logintype'] = 'login';

		tslib_eidtools::connectDB();
		$frontEndUser = tslib_eidtools::initFeUser();
		$frontEndUser->setKey('user', 'onetimeaccount', true);
		$frontEndUser->storeSessionData();

		header('HTTP/1.0 302 Redirect');
		header('Location: ' . $postData['url']);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/onetimeaccount/class.tx_onetimeaccount_eid.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/onetimeaccount/class.tx_onetimeaccount_eid.php']);
}

$instance = t3lib_div::makeInstance('tx_onetimeaccount_eid');
/**
 * @var $instance tx_onetimeaccount_eid
 */
$instance->loginAndRedirect();
?>