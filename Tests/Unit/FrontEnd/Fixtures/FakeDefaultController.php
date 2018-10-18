<?php

namespace OliverKlee\Onetimeaccount\Tests\Unit\FrontEnd\Fixtures;

/**
 * Fake version of the plugin "One-time FE account creator".
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FakeDefaultController extends \tx_onetimeaccount_pi1
{
    /**
     * @var array the simulated form date
     */
    private $formData = [];

    /**
     * Gets the simulated form data for the field $key.
     *
     * @param string $key
     *        key of the field to retrieve, must not be empty and must refer to
     *        an existing form field
     *
     * @return mixed
     *         data for the requested form element or an empty string if the
     *         form field is not set
     */
    public function getFormData($key)
    {
        if (!isset($this->formData[$key])) {
            return '';
        }

        return $this->formData[$key];
    }

    /**
     * Sets the form data.
     *
     * @param array $formData
     *        form data to set as key/value pairs, may be empty
     *
     * @return void
     */
    public function setFormData(array $formData)
    {
        $this->formData = $formData;
    }

    /**
     * Checks if the 'all_names' subpart containing the names label and
     * the name related fields must be hidden.
     *
     * The all_names subpart will be hidden if all name related fields are
     * hidden. These are: 'title', 'name', 'first_name', 'last_name' and
     * 'gender'.
     *
     * @param array &$formFieldsToHide
     *        the form fields which should be hidden, may be empty
     *
     * @return void
     */
    public function setAllNamesSubpartVisibility(array &$formFieldsToHide)
    {
        parent::setAllNamesSubpartVisibility($formFieldsToHide);
    }

    /**
     * Checks if the zip_only subpart must be shown.
     *
     * The zip_only subpart must be shown if the zip is visible but the city
     * is not.
     *
     * @param array &$formFieldsToHide
     *        the form fields which should be hidden, may be empty
     *
     * @return void
     */
    public function setZipSubpartVisibility(array &$formFieldsToHide)
    {
        parent::setZipSubpartVisibility($formFieldsToHide);
    }

    /**
     * Checks if the user group subpart can be hidden.
     *
     * The "usergroup" field is a special case because it might also be
     * hidden if there are less than two user groups available
     *
     * @param array &$formFieldsToHide
     *        the form fields which should be hidden, may be empty
     *
     * @return void
     */
    public function setUserGroupSubpartVisibility(array &$formFieldsToHide)
    {
        parent::setUserGroupSubpartVisibility($formFieldsToHide);
    }

    /**
     * Reads the list of form fields to show from the configuration and stores
     * it in $this->formFieldsToShow.
     *
     * @return void
     */
    public function setFormFieldsToShow()
    {
        parent::setFormFieldsToShow();
    }
}
