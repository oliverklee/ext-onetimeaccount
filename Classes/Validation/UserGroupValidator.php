<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Validation;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Error as ValidationError;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Checks that the provided user group ID is set and within the configured values (if the field is enabled and
 * required).
 */
class UserGroupValidator extends AbstractValidator
{
    /**
     * @var non-empty-string
     */
    private const PROPERTY_KEY = 'userGroup';

    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * @var bool
     */
    private $isRequired = false;

    /**
     * @var array<int, int>
     */
    private $userGroupUids = [];

    /**
     * @param array<string, mixed> $settings
     */
    public function setSettings(array $settings): void
    {
        $fieldsToShowSetting = $settings['fieldsToShow'] ?? '';
        $fieldsToShow = \is_string($fieldsToShowSetting)
            ? GeneralUtility::trimExplode(',', $fieldsToShowSetting, true) : [];

        $requiredFieldsSetting = $settings['requiredFields'] ?? '';
        $requiredFields = \is_string($requiredFieldsSetting)
            ? GeneralUtility::trimExplode(',', $requiredFieldsSetting, true) : [];

        $this->isRequired = \in_array(self::PROPERTY_KEY, $fieldsToShow, true)
            && \in_array(self::PROPERTY_KEY, $requiredFields, true);

        $userGroupSetting = $settings['groupsForNewUsers'] ?? null;
        $this->userGroupUids = \is_string($userGroupSetting)
            ? GeneralUtility::intExplode(',', $userGroupSetting, true) : [];
    }

    protected function isValid($value): void
    {
        if (!$this->isRequired) {
            return;
        }

        $isValid = \is_int($value) && \in_array($value, $this->userGroupUids, true);
        if (!$isValid) {
            $errorMessage = $this->translateErrorMessage('validationError.fillInField', 'oelib') ?? '';
            $error = new ValidationError($errorMessage, 1665681936);
            $this->result->addError($error);
        }
    }
}
