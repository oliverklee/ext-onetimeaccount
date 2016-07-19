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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class checks this extension's configuration for basic sanity.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class Tx_Onetimeaccount_ConfigCheck extends Tx_Oelib_ConfigCheck
{
    /**
     * Checks the configuration for tx_onetimeaccount_pi1.
     *
     * @return void
     */
    protected function check_tx_onetimeaccount_pi1()
    {
        $this->checkStaticIncluded();
        $this->checkTemplateFile(true);
        $this->checkCssFileFromConstants();
        $this->checkSalutationMode();

        $this->checkFeUserFieldsToDisplay();
        $this->checkRequiredFeUserFields();
        $this->checkSystemFolderForNewFeUserRecords();
        $this->checkGroupForNewFeUsers();
        $this->checkUserNameSource();
    }

    /**
     * Checks the setting of the configuration value feUserFieldsToDisplay.
     *
     * @return void
     */
    private function checkFeUserFieldsToDisplay()
    {
        $this->checkIfMultiInSetNotEmpty(
            'feUserFieldsToDisplay',
            true,
            's_general',
            'This value specifies which form fields will be displayed. ' .
                'Incorrect values will cause those fields to not get displayed.',
            $this->getAvailableFields()
        );
    }

    /**
     * Checks the setting of the configuration value requiredFeUserFields.
     *
     * @return void
     */
    private function checkRequiredFeUserFields()
    {
        $this->checkIfMultiInSetOrEmpty(
            'requiredFeUserFields',
            true,
            's_general',
            'This value specifies which form fields are required to be filled in. ' .
                'Incorrect values will cause those fields to not get ' .
                'validated correctly.',
            $this->getAvailableFields(
                [
                    'gender', 'usergroup', 'module_sys_dmail_newsletter',
                    'module_sys_dmail_html',
                ]
            )
        );

        $this->checkIfMultiInSetOrEmpty(
            'requiredFeUserFields',
            true,
            's_general',
            'This value specifies which form fields are required to be filled ' .
                'in. Incorrect values will cause the user not to be able to ' .
                'send the registration form.',
            GeneralUtility::trimExplode(
                ',',
                $this->objectToCheck->getConfValueString(
                    'feUserFieldsToDisplay', 's_general'
                ),
                true
            )
        );
    }

    /**
     * Checks the setting of the configuration value systemFolderForNewFeUserRecords.
     *
     * @return void
     */
    private function checkSystemFolderForNewFeUserRecords()
    {
        $this->checkIfSingleSysFolderNotEmpty(
            'systemFolderForNewFeUserRecords',
            true,
            's_general',
            'This value specifies the system folder in which new FE user' .
                'records will be stored.' .
                'If this value is not set correctly, the records will be ' .
                'stored in the wrong page.'
        );
    }

    /**
     * Checks the setting of the configuration value groupForNewFeUsers.
     *
     * @return void
     */
    private function checkGroupForNewFeUsers()
    {
        $this->checkIfPidListNotEmpty(
            'groupForNewFeUsers',
            true,
            's_general',
            'This value specifies the FE user groups to which new FE user records ' .
                'will be assigned. If this value is not set correctly, the ' .
                'users will not be placed in one of those groups.'
        );
        if ($this->getRawMessage() != '') {
            return;
        }

        $valueToCheck = $this->objectToCheck->getConfValueString(
            'groupForNewFeUsers',
            's_general'
        );
        $groupCounter = Tx_Oelib_Db::selectSingle(
            'COUNT(*) AS number',
            'fe_groups',
            'uid IN (' . $valueToCheck . ')' .
                Tx_Oelib_Db::enableFields('fe_groups')
        );
        $elementsInValueToCheck = count(
            $this->objectToCheck->getUncheckedUidsOfAllowedUserGroups()
        );
        if ($groupCounter['number'] != $elementsInValueToCheck) {
            $this->setErrorMessageAndRequestCorrection(
                'groupForNewFeUsers',
                true,
                'The TS setup variable <strong>' .
                    $this->getTSSetupPath() . 'groupForNewFeUsers</strong> ' .
                    'contains the value ' . $valueToCheck . ' which isn\'t valid. ' .
                    'This value specifies the FE user groups to which new ' .
                    'FE user records will be assigned. ' .
                    'If this value is not set correctly, the users will not ' .
                    'be placed in one of those groups.'
            );
        }
    }

    /**
     * Checks the setting of the configuration value userNameSource.
     *
     * @return void
     */
    private function checkUserNameSource()
    {
        $this->checkIfMultiInSetNotEmpty(
            'userNameSource',
            true,
            's_general',
            'This value specifies how to generate the user name.' .
                'An incorrect value might cause the generated user names look ' .
                'different than intended.',
            ['email', 'name']
        );
    }

    /**
     * Returns an array of field names that are provided in the form AND that
     * actually exist in the DB (some fields need to be provided by
     * sr_feuser_register).
     *
     * @param array $excludeFields
     *        fields which should be excluded from the list of available fields,
     *        may be empty
     *
     * @return array list of available field names, will not be empty
     */
    private function getAvailableFields(array $excludeFields = [])
    {
        $providedFields = [
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
        $formFields = array_diff($providedFields, $excludeFields);
        $fieldsFromFeUsers = $this->getDbColumnNames('fe_users');

        // Makes sure that only fields are allowed that are actually available.
        // (Some fields don't come with the vanilla TYPO3 installation and are
        // provided by the sr_feusers_register extension.)
        return array_intersect($formFields, $fieldsFromFeUsers);
    }
}
