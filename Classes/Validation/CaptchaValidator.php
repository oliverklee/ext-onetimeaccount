<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Validation;

use OliverKlee\Onetimeaccount\Domain\Model\Captcha;
use OliverKlee\Onetimeaccount\Service\CaptchaFactory;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Validation\Error as ValidationError;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;

/**
 * Validates that the captcha is filled in correctly (if it is enabled via the configuration).
 */
class CaptchaValidator extends AbstractValidator
{
    protected $acceptsEmptyValues = false;

    /**
     * @var CaptchaFactory
     */
    private $captchaFactory;

    /**
     * @var bool
     */
    private $enabled = false;

    public function injectCaptchaFactory(CaptchaFactory $factory): void
    {
        $this->captchaFactory = $factory;
    }

    /**
     * @param array<string, string> $settings
     */
    public function setSettings(array $settings): void
    {
        $captchaSetting = $settings['captcha'] ?? '0';
        if (\is_string($captchaSetting)) {
            $this->enabled = (bool)$captchaSetting;
        }
    }

    /**
     * @param mixed $value
     */
    protected function isValid($value): void
    {
        if (!$this->enabled) {
            return;
        }

        if (!$value instanceof Captcha) {
            $this->markAsInvalid();
            return;
        }

        $validUntil = $value->getValidUntil();
        if (!$validUntil instanceof \DateTime) {
            $this->markAsInvalid();
            return;
        }

        $now = GeneralUtility::makeInstance(Context::class)->getPropertyFromAspect('date', 'full');
        if ($validUntil < $now) {
            $this->markAsInvalid();
            return;
        }

        $this->captchaFactory->fillCorrectAnswer($value);
        if ($value->getCorrectAnswer() !== $value->getGivenAnswer()) {
            $this->markAsInvalid();
        }
    }

    private function markAsInvalid(): void
    {
        $errorMessage = $this->translateErrorMessage('captcha.validationError', 'onetimeaccount');
        $error = new ValidationError($errorMessage, 1671644868);
        $this->result->forProperty('givenAnswer')->addError($error);
    }
}
