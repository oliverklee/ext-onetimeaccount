<?php
/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

/**
 * Class that adds the wizard icon.
 *
 * @package TYPO3
 * @subpackage tx_onetimeaccount
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class tx_onetimeaccount_pi1_wizicon {
	/**
	 * Processes the wizard items array.
	 *
	 * @param array $wizardItems the wizard items, may be empty
	 *
	 * @return array modified array with wizard items
	 */
	public function proc(array $wizardItems) {
		$languageData = $this->includeLocalLang();

		/** @var language $languageService */
		$languageService = $GLOBALS['LANG'];
		$wizardItems['plugins_tx_onetimeaccount_pi1'] = array(
			'icon' => t3lib_extMgm::extRelPath('onetimeaccount') . 'pi1/ce_wiz.gif',
			'title' => $languageService->getLLL('pi1_title', $languageData),
			'description' => $languageService->getLLL('pi1_description', $languageData),
			'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=onetimeaccount_pi1',
		);

		return $wizardItems;
	}

	/**
	 * Reads the [extDir]/locallang.xml and returns the $LOCAL_LANG array found
	 * in that file.
	 *
	 * @return array the found language labels
	 */
	public function includeLocalLang() {
		$languageFile = t3lib_extMgm::extPath('onetimeaccount') . 'locallang.xml';
		/** @var language $languageService */
		$languageService = $GLOBALS['LANG'];
		if (class_exists('t3lib_l10n_parser_Llxml')) {
			/** @var $xmlParser t3lib_l10n_parser_Llxml */
			$xmlParser = t3lib_div::makeInstance('t3lib_l10n_parser_Llxml');
			$localLanguage = $xmlParser->getParsedData($languageFile, $languageService->lang);
		} else {
			$localLanguage = t3lib_div::readLLXMLfile($languageFile, $languageService->lang);
		}

		return $localLanguage;
	}
}

if (defined('TYPO3_MODE') && $GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/onetimeaccount/pi1/class.tx_onetimeaccount_pi1_wizicon.php']) {
	include_once($GLOBALS['TYPO3_CONF_VARS'][TYPO3_MODE]['XCLASS']['ext/onetimeaccount/pi1/class.tx_onetimeaccount_pi1_wizicon.php']);
}