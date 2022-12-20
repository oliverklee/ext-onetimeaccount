<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Validation;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Oelib\Validation\AbstractConfigurationDependentValidator;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * Checks that the fields that are configured to be required are filled in.
 *
 * @extends AbstractConfigurationDependentValidator<FrontendUser>
 */
class UserValidator extends AbstractConfigurationDependentValidator
{
    protected function getModelClassName(): string
    {
        return FrontendUser::class;
    }

    protected function isFieldFilledIn(string $field, AbstractEntity $model): bool
    {
        return $this->isIdentityFieldFilledInForUser($field, $model)
            && $this->isAddressFieldFilledInForUser($field, $model)
            && $this->isContactFieldFilledInForUser($field, $model)
            && $this->isMetaFieldFilledInForUser($field, $model);
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
                $result = $user->getDateOfBirth() instanceof \DateTimeInterface;
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
