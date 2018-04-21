<?php
namespace OliverKlee\Onetimeaccount\BackEnd;

use TYPO3\CMS\Core\Localization\Parser\XliffParser;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Lang\LanguageService;

/**
 * Class that adds the wizard icon.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class ContentElementWizard
{
    /**
     * Processes the wizard items array.
     *
     * @param array $wizardItems the wizard items, may be empty
     *
     * @return array modified array with wizard items
     */
    public function proc(array $wizardItems)
    {
        $languageData = $this->includeLocalLang();

        /** @var LanguageService $languageService */
        $languageService = $GLOBALS['LANG'];
        $wizardItems['plugins_tx_onetimeaccount_pi1'] = [
            'icon' => ExtensionManagementUtility::extRelPath('onetimeaccount')
                . 'Resources/Public/Icons/ContentElementWizard.gif',
            'title' => $languageService->getLLL('pi1_title', $languageData),
            'description' => $languageService->getLLL('pi1_description', $languageData),
            'params' => '&defVals[tt_content][CType]=list&defVals[tt_content][list_type]=onetimeaccount_pi1',
        ];

        return $wizardItems;
    }

    /**
     * Reads the locallang file and returns the $LOCAL_LANG array found in that file.
     *
     * @return array the found language labels
     */
    public function includeLocalLang()
    {
        $languageFile = ExtensionManagementUtility::extPath('onetimeaccount') . 'Resources/Private/Language/locallang.xlf';
        /** @var LanguageService $languageService */
        $languageService = $GLOBALS['LANG'];
        /** @var XliffParser $xmlParser */
        $xmlParser = GeneralUtility::makeInstance(XliffParser::class);
        $localLanguage = $xmlParser->getParsedData($languageFile, $languageService->lang);

        return $localLanguage;
    }
}
