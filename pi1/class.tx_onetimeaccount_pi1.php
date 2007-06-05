<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007 Oliver Klee <typo3-coding@oliverklee.de>
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

require_once(t3lib_extMgm::extPath('oelib').'class.tx_oelib_templatehelper.php');

/**
 * Plugin 'One-time FE account creator' for the 'onetimeaccount' extension.
 *
 * @author	Oliver Klee <typo3-coding@oliverklee.de>
 * @package	TYPO3
 * @subpackage	tx_onetimeaccount
 */
class tx_onetimeaccount_pi1 extends tx_oelib_templatehelper {
	var $prefixId = 'tx_onetimeaccount_pi1';
	var $scriptRelPath = 'pi1/class.tx_onetimeaccount_pi1.php';
	var $extKey = 'onetimeaccount';

	/**
	 * Creates the plugin output.
	 *
	 * @param	string		(ignored)
	 * @param	array		the plug-in configuration
	 *
	 * @return	string		HTML output of the plug-in
	 */
	function main($content, $conf)	{
		$this->init($conf);
		$this->pi_initPIflexForm();

		$this->addCssToPageHeader();

		// disable caching
		$this->pi_USER_INT_obj = 1;

		$result = '<p>Hello world!</p>';
		$result .= $this->checkConfiguration();

		return $this->pi_wrapInBaseClass($result);
	}
}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/onetimeaccount/pi1/class.tx_onetimeaccount_pi1.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/onetimeaccount/pi1/class.tx_onetimeaccount_pi1.php']);
}

?>
