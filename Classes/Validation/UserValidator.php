<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Validation;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Error as ValidationError;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Checks that the fields that are configured to be required are filled in.
 */
class UserValidator extends AbstractValidator
{
    /**
     * @var bool
     */
    protected $acceptsEmptyValues = false;

    /**
     * @var array<int, string>
     */
    private $requiredFields = [];

    /**
     * @param array<string, string> $settings
     */
    public function setSettings(array $settings): void
    {
        $requiredFieldsSetting = $settings['requiredFields'] ?? '';
        if (\is_string($requiredFieldsSetting)) {
            $this->requiredFields = GeneralUtility::trimExplode(',', $requiredFieldsSetting, true);
        }
    }

    /**
     * @param mixed $user
     */
    protected function isValid($user): void
    {
        if (!$user instanceof FrontendUser) {
            return;
        }

        foreach ($this->requiredFields as $field) {
            if (!$this->isFieldFilledForInUser($field, $user)) {
                $error = new ValidationError('validationError.fillInField', 1651765504);
                $this->result->forProperty($field)->addError($error);
            }
        }
    }

    private function isFieldFilledForInUser(string $field, FrontendUser $user): bool
    {
        return $this->isIdentityFieldFilledInForUser($field, $user)
            && $this->isAddressFieldFilledInForUser($field, $user)
            && $this->isContactFieldFilledInForUser($field, $user)
            && $this->isMetaFieldFilledInForUser($field, $user);
    }

    private function isIdentityFieldFilledInForUser(string $field, FrontendUser $user): bool
    {
        switch ($field) {
            case 'name':
                $result = $user->getName() !== '';
                break;
            case 'firstName':
                $result = $user->getFirstName() !== '';
                break;
            case 'lastName':
                $result = $user->getLastName() !== '';
                break;
            case 'title':
                $result = $user->getTitle() !== '';
                break;
            case 'fullSalutation':
                $result = $user->getFullSalutation() !== '';
                break;
            case 'gender':
                $result = $user->getGender() !== FrontendUser::GENDER_NOT_PROVIDED;
                break;
            case 'dateOfBirth':
                $result = $user->getDateOfBirth() instanceof \DateTime;
                break;
            case 'status':
                $result = $user->getStatus() !== FrontendUser::STATUS_NONE;
                break;
            default:
                $result = true;
        }

        return $result;
    }

    private function isAddressFieldFilledInForUser(string $field, FrontendUser $user): bool
    {
        switch ($field) {
            case 'address':
                $result = $user->getAddress() !== '';
                break;
            case 'zip':
                $result = $user->getZip() !== '';
                break;
            case 'city':
                $result = $user->getCity() !== '';
                break;
            case 'zone':
                $result = $user->getZone() !== '';
                break;
            case 'country':
                $result = $user->getCountry() !== '';
                break;
            default:
                $result = true;
        }

        return $result;
    }

    private function isContactFieldFilledInForUser(string $field, FrontendUser $user): bool
    {
        switch ($field) {
            case 'telephone':
                $result = $user->getTelephone() !== '';
                break;
            case 'email':
                $result = $user->getEmail() !== '';
                break;
            case 'www':
                $result = $user->getWww() !== '';
                break;
            default:
                $result = true;
        }

        return $result;
    }

    private function isMetaFieldFilledInForUser(string $field, FrontendUser $user): bool
    {
        switch ($field) {
            case 'company':
                $result = $user->getCompany() !== '';
                break;
            case 'privacy':
                $result = $user->getPrivacy();
                break;
            case 'status':
                $result = $user->getStatus() !== FrontendUser::STATUS_NONE;
                break;
            case 'comments':
                $result = $user->getComments() !== '';
                break;
            default:
                $result = true;
        }

        return $result;
    }
}
