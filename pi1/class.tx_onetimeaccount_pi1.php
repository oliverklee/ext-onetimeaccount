<?php

use SJBR\StaticInfoTables\PiBaseApi;
use SJBR\StaticInfoTables\Utility\LocalizationUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Authentication\FrontendUserAuthentication;

/**
 * Plugin "One-time FE account creator".
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 * @author Niels Pardon <mail@niels-pardon.de>
 */
class tx_onetimeaccount_pi1 extends Tx_Oelib_TemplateHelper implements Tx_Oelib_Interface_ConfigurationCheckable
{
    /**
     * make the plugin uncached
     *
     * @var bool
     */
    public $pi_USER_INT_obj = true;

    /**
     * @var string same as class name
     */
    public $prefixId = 'tx_onetimeaccount_pi1';
    /**
     * @var string path to this script relative to the extension dir
     */
    public $scriptRelPath = 'pi1/class.tx_onetimeaccount_pi1.php';
    /**
     * @var string the extension key
     */
    public $extKey = 'onetimeaccount';

    /**
     * @var \tx_mkforms_forms_Base
     */
    protected $form = null;

    /**
     * @var array names of the form fields to show
     */
    private $formFieldsToShow = [];

    /**
     * @var array names of the form fields that are required to be filled in
     */
    private $requiredFormFields = [];

    /**
     * @var PiBaseApi
     */
    private $staticInfo = null;

    /**
     * @var array the fields available in the form
     */
    private static $availableFormFields = [
        'company',
        'gender',
        'title',
        'name',
        'first_name',
        'last_name',
        'address',
        'zip',
        'city',
        'zone',
        'country',
        'static_info_country',
        'email',
        'www',
        'telephone',
        'fax',
        'date_of_birth',
        'status',
        'module_sys_dmail_newsletter',
        'module_sys_dmail_html',
        'usergroup',
        'comments',
    ];

    /**
     * Creates the plugin output.
     *
     * @param string $content (ignored)
     * @param array $configuration the plug-in configuration
     *
     * @return string HTML output of the plug-in
     */
    public function main($content, array $configuration)
    {
        $this->init($configuration);
        $this->pi_initPIflexForm();

        $this->initializeFormFields();
        $this->initializeForm();

        $result = $this->renderForm() . $this->checkConfiguration();

        return $this->pi_wrapInBaseClass($result);
    }

    /**
     * Creates and initializes the FORMidable object.
     *
     * @return void
     */
    protected function initializeForm()
    {
        \tx_rnbase::load(\tx_mkforms_forms_Base::class);
        \tx_rnbase::load(\Tx_Rnbase_Database_Connection::class);
        $this->form = GeneralUtility::makeInstance(\tx_mkforms_forms_Base::class);

        /** @var \Tx_Rnbase_Configuration_Processor $pluginConfiguration */
        $pluginConfiguration = GeneralUtility::makeInstance(\Tx_Rnbase_Configuration_Processor::class);
        $pluginConfiguration->init($this->conf, $this->cObj, 'onetimeaccount', 'tx_onetimeaccount_pi1_form');

        $this->form->initFromTs(
            $this,
            $this->conf['form.'],
            false,
            $pluginConfiguration,
            'form.'
        );
    }

    /**
     * Initializes which form fields should be shown and which are required.
     *
     * @return void
     */
    private function initializeFormFields()
    {
        $this->setFormFieldsToShow();
        $this->setRequiredFormFields();
        $this->setRequiredFieldLabels();
    }

    /**
     * Reads the list of form fields to show from the configuration and stores
     * it in $this->formFieldsToShow.
     *
     * @return void
     */
    protected function setFormFieldsToShow()
    {
        $this->formFieldsToShow = GeneralUtility::trimExplode(
            ',',
            $this->getConfValueString('feUserFieldsToDisplay', 's_general')
        );
    }

    /**
     * Reads the list of required form fields from the configuration and stores
     * it in $this->requiredFormFields.
     *
     * @return void
     */
    private function setRequiredFormFields()
    {
        $this->requiredFormFields = GeneralUtility::trimExplode(
            ',',
            $this->getConfValueString('requiredFeUserFields', 's_general')
        );
    }

    /**
     * Gets the path to the HTML template as set in the TS setup or flexforms.
     * The returned path will always be an absolute path in the file system;
     * EXT: references will automatically get resolved.
     *
     * @return string
     *         the path to the HTML template as an absolute path in the file
     *         system, will not be empty in a correct configuration
     */
    public function getTemplatePath()
    {
        return GeneralUtility::getFileAbsFileName(
            $this->getConfValueString('templateFile', 's_template_special', true)
        );
    }

    /**
     * Creates the HTML output of the form.
     *
     * @return string HTML of the form
     */
    private function renderForm()
    {
        $rawForm = $this->form->render();

        $this->processTemplate($rawForm);
        $this->hideUnusedFormFields();

        return $this->getSubpart();
    }

    /**
     * Hides form fields that are disabled via TS setup from the templating
     * process.
     *
     * @return void
     */
    private function hideUnusedFormFields()
    {
        $formFieldsToHide = array_diff(
            self::$availableFormFields,
            $this->formFieldsToShow
        );

        $this->setUserGroupSubpartVisibility($formFieldsToHide);
        $this->setZipSubpartVisibility($formFieldsToHide);
        $this->setAllNamesSubpartVisibility($formFieldsToHide);

        $this->hideSubpartsArray($formFieldsToHide, 'wrapper');
    }

    /**
     * Checks whether a form field should be displayed (and evaluated) at all.
     * This is specified via TS setup (or flexforms) using the
     * "feUserFieldsToDisplay" variable.
     * Radiobuttons to choose user groups are only shown if there is more than
     * one value to display.
     *
     * @param array $parameters
     *        the contents of the "params" child of the userobj node as
     *        key/value pairs (used for retrieving the current form field name)
     *
     * @return bool
     *         TRUE if the current form field should be displayed, FALSE otherwise
     */
    public function isFormFieldEnabled(array $parameters)
    {
        $key = $parameters['elementName'];
        $result = in_array($key, $this->formFieldsToShow, true);
        if ($key === 'usergroup') {
            $result = $result && $this->hasAtLeastTwoUserGroups();
        }
        return $result;
    }

    /**
     * Provides a localized list of localized country names from static_tables.
     *
     * If $parameters['alpha3'] is set, the alpha3 codes will be used as form
     * values. Otherwise, the localized country names will be used as values.
     *
     * @param array $parameters
     *        contents of the "params" XML child of the userobj node (needs to
     *        contain an element with the key "key")
     *
     * @return array
     *         localized country names from static_tables as an array with the
     *         keys "caption" (for the localized title) and "value" (either the
     *         country's alpha3 code or the localized name)
     */
    public function populateListCountries(array $parameters)
    {
        $this->initStaticInfo();
        $allCountries = $this->staticInfo->initCountries('ALL', '', true);

        $result = [];
        // Add an empty item at the top so we won't have Afghanistan (the first
        // entry) pre-selected for empty values.
        $result[] = [
            'caption' => '',
            'value' => '',
        ];

        foreach ($allCountries as $alpha3Code => $currentCountryName) {
            $result[] = [
                'caption' => $currentCountryName,
                'value' => isset($parameters['alpha3'])
                    ? $alpha3Code : $currentCountryName,
            ];
        }

        return $result;
    }

    /**
     * Returns the default country as alpha3 code or localized string.
     *
     * If $parameters['alpha3'] is set, the alpha3 code will be used as return
     * value. Otherwise, the localized country name will be used as return value.
     *
     * @param array $parameters
     *        contents of the "params" XML child of the userobj node (needs to
     *        contain an element with the key "key")
     *
     * @return string
     *         the default country (either the country's alpha3 code or the
     *         localized name), will be empty if no default country has been set
     */
    public function getDefaultCountry(array $parameters)
    {
        $defaultCountryCode = Tx_Oelib_ConfigurationRegistry::get('plugin.tx_staticinfotables_pi1')
            ->getAsString('countryCode');
        if ($defaultCountryCode === '') {
            return '';
        }

        $this->initStaticInfo();

        if ($parameters['alpha3']) {
            $result = $defaultCountryCode;
        } else {
            $currentLanguageCode = Tx_Oelib_ConfigurationRegistry::get('config')->getAsString('language');
            $identifiers = ['iso' => $defaultCountryCode];
            $result = LocalizationUtility::getLabelFieldValue($identifiers, 'static_countries', $currentLanguageCode, true);
        }

        return $result;
    }

    /**
     * Creates and initializes $this->staticInfo (if that hasn't been done yet).
     *
     * @return void
     */
    private function initStaticInfo()
    {
        if ($this->staticInfo === null) {
            $this->staticInfo = GeneralUtility::makeInstance(PiBaseApi::class);
            $this->staticInfo->init();
        }
    }

    /**
     * Gets the PID of the system folder in which new FE user records will be
     * stored.
     *
     * @return int the PID of the page where FE-created events will be stored
     */
    public function getPidForNewUserRecords()
    {
        return $this->getConfValueInteger(
            'systemFolderForNewFeUserRecords',
            's_general'
        );
    }

    /**
     * Creates a session for the created FE user and returns the redirect URL after the form data has been submitted
     * and validated.
     *
     * The returned URL is either the URL provided in as the GET parameter
     * "redirect_url" or the current page if the redirect URL is empty.
     *
     * @return string the fully-qualified URL to redirect to, will not be empty
     */
    public function loginUserAndCreateRedirectUrl()
    {
        $this->workAroundModSecurity();

        $url = GeneralUtility::sanitizeLocalUrl((string) GeneralUtility::_GP('redirect_url'));
        if ($url === '') {
            $url = GeneralUtility::getIndpEnv('TYPO3_REQUEST_URL');
            $this->log('redirect_url is empty, using the request URL: ' . $url, 2);
        }

        /** @var FrontendUserAuthentication $frontEndUser */
        $frontEndUser = $this->getFrontEndController()->fe_user;
        $frontEndUser->checkPid = false;

        $authenticationData = $this->getFrontEndController()->fe_user->getAuthInfoArray();
        $userData = $frontEndUser->fetchUserRecord($authenticationData['db_user'], $this->getFormData('username'));
        $frontEndUser->user = $userData;
        $frontEndUser->createUserSession($userData);
        $frontEndUser->setKey('user', 'onetimeaccount', true);
        // fake a session entry to ensure the Core actually creates the session and sends the FE cookie
        $frontEndUser->setKey('ses', 'onetimeaccount_dummy', true);
        $frontEndUser->storeSessionData();

        $this->log('Redirecting to: ' . $url);

        return $url;
    }

    /**
     * Tries to get the redirect_url GET variable from the request URI if
     * this is possible and the GET variable otherwise would be empty.
     *
     * This might happen with certain mod_security rules that drop all GET
     * variables if a fully-qualified URL is set in one variable.
     *
     * @return void
     */
    private function workAroundModSecurity()
    {
        if (isset($GLOBALS['_GET']['redirect_url']) || !isset($GLOBALS['_SERVER']['REQUEST_URI'])) {
            return;
        }
        $this->log('Applying mod_security workaround.', 1);

        $matches = [];
        preg_match('/(^\\?|&)(redirect_url=)([^&]+)(&|$)/', $GLOBALS['_SERVER']['REQUEST_URI'], $matches);
        if (!empty($matches)) {
            $GLOBALS['_GET']['redirect_url'] = rawurldecode($matches[3]);
        }
    }

    /**
     * Gets the entered form data for the field $key.
     *
     * @param string $key
     *        key of the field to retrieve, must not be empty and must refer to
     *        an existing form field
     *
     * @return mixed data for the requested form element
     */
    protected function getFormData($key)
    {
        /** @var \formidable_maindatahandler $dataHandler */
        $dataHandler = $this->form->oDataHandler;
        return $dataHandler->getThisFormData($key);
    }

    /**
     * Creates a unique FE user name. It consists of the entered e-mail address.
     * If a user with that user name already exists, a number will be appended.
     *
     * @return string a user name, will not be empty
     */
    public function getUserName()
    {
        $initialUsername = $this->createInitialUserName();
        $numberToAppend = 1;
        $result = $initialUsername;

        /** @var FrontendUserAuthentication $frontEndUser */
        $frontEndUser = $this->getFrontEndController()->fe_user;
        // Modify the user name until we have a unique user name.
        while ($frontEndUser->getRawUserByName($result)) {
            $result = $initialUsername . '-' . $numberToAppend;
            $numberToAppend++;
        }

        return $result;
    }

    /**
     * Creates the initial user name, i.e. the first part of the user name
     * to which then a suffix like "-2" might get appended to make it unique.
     *
     * @return string an initial user name, is not guaranteed to be unique
     */
    public function createInitialUserName()
    {
        if ($this->getConfValueString('userNameSource', 's_general') === 'name') {
            $fullName = (string) $this->getFormData('name');
            if ($fullName === '') {
                $fullName = $this->getFormData('first_name') . ' ' . $this->getFormData('last_name');
            }

            $lowercasedName = mb_strtolower($fullName, 'UTF-8');
            $safeLowercasedName = preg_replace('/[^a-z ]/', '', $lowercasedName);
            $userNameParts = GeneralUtility::trimExplode(' ', $safeLowercasedName, true);
            $userName = implode('.', $userNameParts);
        } else {
            $userName = trim((string) $this->getFormData('email'));
        }

        if ($userName === '') {
            $userName = 'user';
        }

        return $userName;
    }

    /**
     * Creates a random 8-character password, consisting of digits, uppercase
     * and lowercase characters and some special chars.
     *
     * @return string a random 8 character password
     */
    public function getPassword()
    {
        $result = '';

        $availableCharacters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!$%&/()=?*+#,;.:-_<>';
        $indexOfLastCharacter = strlen($availableCharacters) - 1;

        for ($i = 0; $i < 8; $i++) {
            $result .= $availableCharacters[mt_rand(0, $indexOfLastCharacter)];
        }

        return $result;
    }

    /**
     * Makes some preprocessing which is necessary to insert the user into the DB.
     *
     * @param array $formData
     *        entered form data, may be empty
     *
     * @return array processed form data, will not be empty
     */
    public function preprocessFormData(array $formData)
    {
        $this->log('Submitted data is valid on FE page: ' . $this->getFrontEndController()->id);

        $result = $this->setCurrentUserGroup($formData);
        $result = $this->buildFullName($result);

        $this->log(
            'Creating user "' . $result['username'] . '" with groups ' .
                $result['usergroup'] . ' in sysfolder ' . $result['pid'] . '.'
        );

        return $result;
    }

    /**
     * Gets the form data and adds the user group(s) from the BE configuration
     * if the form field to choose a user group in the FE is disabled.
     *
     * @param array $formData
     *        entered form data, may be empty
     *
     * @return array
     *         form data: If choosing user groups in in FE is disabled, the user
     *         group(s) of groupForNewFeUsers are added to the form data,
     *         otherwise it is returned without modifications.
     */
    public function setCurrentUserGroup(array $formData)
    {
        if (!empty($formData['usergroup'])) {
            return $formData;
        }

        $result = $formData;

        if (!$this->isFormFieldEnabled(['elementname' => 'usergroup'])) {
            $result['usergroup'] = $this->getConfValueString(
                'groupForNewFeUsers',
                's_general');
        }

        return $result;
    }

    /**
     * Returns the UID of the first user group shown in the FE. If there are no
     * user groups, the result will be zero.
     *
     * @return int UID of the first user group
     */
    public function getUidOfFirstUserGroup()
    {
        $userGroups = $this->getUncheckedUidsOfAllowedUserGroups();

        return $userGroups[0];
    }

    /**
     * Returns the user groups choosable in the front end.
     *
     * @return array
     *         user groups choosable in the FE, will not be empty if configured
     *         correctly
     */
    public function listUserGroups()
    {
        $listOfUserGroupUids = $this->getConfValueString('groupForNewFeUsers', 's_general');
        if (($listOfUserGroupUids === '') || !preg_match('/^(\\d+(,( *)\\d+)*)?$/', $listOfUserGroupUids)) {
            return [];
        }

        $result = [];
        $groupData = Tx_Oelib_Db::selectMultiple(
            'uid, title',
            'fe_groups',
            'uid IN(' . $listOfUserGroupUids . ')' .
                Tx_Oelib_Db::enableFields('fe_groups')
        );

        foreach ($groupData as $item) {
            $result[] = [
                'caption' => $item['title'],
                'value' => $item['uid'],
            ];
        }

        return $result;
    }

    /**
     * Gets the UIDs set via groupForNewFeUsers in the configuration.
     *
     * @return int[]
     *         UIDs set via groupForNewFeUsers, will not be empty for a valid
     *         configuration
     */
    public function getUncheckedUidsOfAllowedUserGroups()
    {
        return GeneralUtility::intExplode(',', $this->getConfValueString('groupForNewFeUsers', 's_general'), true);
    }

    /**
     * Checks whether a radiobutton in a radiobutton group is selected.
     *
     * @param array $radioGroupValue
     *        the currently selected value in an associative array with the key "value"
     *
     * @return bool
     *         TRUE if a radiobutton is selected or if the form field is hidden,
     *         FALSE if none is selected although the field is visible
     */
    public function isRadiobuttonSelected(array $radioGroupValue)
    {
        if (!$this->isFormFieldEnabled(['elementname' => 'usergroup'])) {
            return true;
        }

        $allowedValues = $this->getUncheckedUidsOfAllowedUserGroups();

        return in_array((int)$radioGroupValue['value'], $allowedValues, true);
    }

    /**
     * Checks whether we have at least two allowed user groups.
     *
     * @return bool
     *         TRUE if we have at least two allowed user groups, FALSE otherwise
     */
    private function hasAtLeastTwoUserGroups()
    {
        return (count($this->listUserGroups()) > 1);
    }

    /**
     * Adds a class 'required' to the label of a field if it is required.
     *
     * @return void
     */
    private function setRequiredFieldLabels()
    {
        $formFieldsToCheck = array_diff(
            self::$availableFormFields,
            [
                'usergroup', 'gender', 'module_sys_dmail_newsletter',
                'module_sys_dmail_html',
            ]
        );
        foreach ($formFieldsToCheck as $formField) {
            $this->setMarker(
                $formField . '_required',
                in_array($formField, $this->requiredFormFields, true) ? ' class="required"' : ''
            );
        }
    }

    /**
     * Checks whether the content of a given field is non-empty or not required.
     *
     * @param array $formData
     *        associative array containing the current value with the key
     *        "value" and the name with the key "elementName" of the form field
     *        to check, must not be empty
     *
     * @return bool
     *         TRUE if this field is not empty or not required, FALSE otherwise
     */
    public function validateStringField(array $formData)
    {
        if ($this->checkPremises($formData)) {
            return true;
        }

        return trim($formData['value']) !== '';
    }

    /**
     * Checks whether the content of a given field is non-zero or not required.
     *
     * @param array $formData
     *        associative array containing the current value with the key
     *        "value" and the name with the key "elementName" of the form field
     *        to check, must not be empty
     *
     * @return bool
     *         TRUE if this field is not zero or not required, FALSE otherwise
     */
    public function validateIntegerField(array $formData)
    {
        if ($this->checkPremises($formData)) {
            return true;
        }

        return (int)$formData['value'] !== 0;
    }

    /**
     * Checks if the form field data is not empty and if it is required.
     *
     * @param array $formData
     *        associative array containing the current value with the key
     *        "value" and the name with the key "elementName" of the form field
     *        to check, must not be empty
     *
     * @return bool TRUE if the element was not required, FALSE otherwise
     *
     * @throws InvalidArgumentException
     */
    private function checkPremises(array $formData)
    {
        if (empty($formData['elementName'])) {
            throw new InvalidArgumentException('The given field name was empty.');
        }

        if (empty($this->requiredFormFields)) {
            $this->setRequiredFormFields();
        }

        return !in_array($formData['elementName'], $this->requiredFormFields, true);
    }

    /**
     * Checks if the usergroup subpart can be hidden.
     *
     * The "usergroup" field is a special case because it might also be
     * hidden if there are less than two user groups available
     *
     * If the subpart is hidden it will be added to formFieldsToHide
     *
     * @param array &$formFieldsToHide
     *        the form fields which should be hidden, may be empty
     *
     * @return void
     */
    protected function setUserGroupSubpartVisibility(array &$formFieldsToHide)
    {
        if (!$this->hasAtLeastTwoUserGroups()) {
            $formFieldsToHide[] = 'usergroup';
        }
    }

    /**
     * Checks if the zip_only subpart must be shown.
     *
     * The zip_only subpart must be shown if the zip is visible but the city
     * is not.
     *
     * If the subpart is hidden it will be added to formFieldsToHide
     *
     * @param array &$formFieldsToHide
     *        the form fields which should be hidden, may be empty
     *
     * @return void
     */
    protected function setZipSubpartVisibility(array &$formFieldsToHide)
    {
        if (!in_array('city', $formFieldsToHide, true) || in_array('zip', $formFieldsToHide, true)) {
            $formFieldsToHide[] = 'zip_only';
        }
    }

    /**
     * Checks if the 'all_names' subpart containing the names label and
     * the name related fields must be hidden.
     *
     * The all_names subpart will be hidden if all name related fields are
     * hidden. These are: 'title', 'name', 'first_name', 'last_name' and
     * 'gender'.
     *
     * If the subpart is hidden, it will be added to $formFieldsToHide.
     *
     * @param array &$formFieldsToHide
     *        the form fields which should be hidden, may be empty
     *
     * @return void
     */
    protected function setAllNamesSubpartVisibility(array &$formFieldsToHide)
    {
        $nameRelatedFields = ['name', 'first_name', 'last_name', 'gender'];

        $visibleNameFields = array_diff(
            $nameRelatedFields,
            array_intersect($formFieldsToHide, $nameRelatedFields)
        );

        if (empty($visibleNameFields)) {
            $formFieldsToHide[] = 'all_names';
        }
    }

    /**
     * Builds the name field.
     *
     * If the name field is hidden, the name will be built from the 'first_name'
     * and 'last_name'.
     *
     * @param array $formData the form data sent, may be empty
     *
     * @return array
     *         the form data with the modified name field, will be empty
     *         if the given form data was empty
     */
    private function buildFullName(array $formData)
    {
        if (in_array('name', $this->formFieldsToShow, true)) {
            return $formData;
        }

        $firstName = in_array('first_name', $this->formFieldsToShow, true) ? $formData['first_name'] : '';
        $lastName = in_array('last_name', $this->formFieldsToShow, true) ? $formData['last_name'] : '';

        $formData['name'] = trim($firstName . ' ' . $lastName);

        return $formData;
    }

    /**
     * Logs $message to the TYPO3 development log if logging is enabled for
     * this extension.
     *
     * @param string $message the message to log, must not be empty
     * @param int $severity
     *        0 = info, 1 = notice, 2 = warning, 3 = fatal error, -1 = OK
     *
     * @return void
     */
    private function log($message, $severity = 0)
    {
        if (!Tx_Oelib_ConfigurationProxy::getInstance('onetimeaccount')->getAsBoolean('enableLogging')) {
            return;
        }

        GeneralUtility::devLog($message, 'onetimeaccount', $severity);
    }

    /**
     * Returns the prefix for the configuration to check, e.g. "plugin.tx_seminars_pi1.".
     *
     * @return string the namespace prefix, will end with a dot
     */
    public function getTypoScriptNamespace()
    {
        return 'plugin.tx_onetimeaccount_pi1.';
    }
}
