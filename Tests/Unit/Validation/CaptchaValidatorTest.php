<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Validation;

use OliverKlee\Onetimeaccount\Service\CaptchaFactory;
use OliverKlee\Onetimeaccount\Validation\CaptchaValidator;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Validation\CaptchaValidator
 */
final class CaptchaValidatorTest extends UnitTestCase
{
    /**
     * @var CaptchaValidator
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new CaptchaValidator();

        $this->subject->injectCaptchaFactory(new CaptchaFactory());
    }

    /**
     * @test
     */
    public function isValidator(): void
    {
        self::assertInstanceOf(ValidatorInterface::class, $this->subject);
        self::assertInstanceOf(AbstractValidator::class, $this->subject);
    }

    /**
     * @test
     */
    public function isSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }

    /**
     * @test
     */
    public function validateWithNullAndCaptchaNotEnabledReturnsNoErrors(): void
    {
        $result = $this->subject->validate(null);

        self::assertFalse($result->hasErrors());
    }
}
