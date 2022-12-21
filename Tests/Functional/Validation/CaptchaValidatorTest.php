<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Validation;

use OliverKlee\Onetimeaccount\Domain\Model\Captcha;
use OliverKlee\Onetimeaccount\Service\CaptchaFactory;
use OliverKlee\Onetimeaccount\Validation\CaptchaValidator;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Error\Result;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Validation\CaptchaValidator
 */
final class CaptchaValidatorTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/onetimeaccount',
    ];

    protected $coreExtensionsToLoad = ['extbase', 'fluid'];

    /**
     * @var CaptchaValidator
     */
    private $subject;

    /**
     * @var CaptchaFactory
     */
    private $captchaFactory;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['LANG'] = $this->get(LanguageService::class);

        $this->subject = new CaptchaValidator();

        $this->captchaFactory = new CaptchaFactory();
        $this->subject->injectCaptchaFactory($this->captchaFactory);
    }

    private static function assertCaptchaValidationError(Result $result): void
    {
        self::assertTrue($result->hasErrors());
        $forProperty = $result->forProperty('givenAnswer');
        self::assertCount(1, $forProperty->getErrors());
        $firstError = $forProperty->getFirstError();
        self::assertInstanceOf(Error::class, $firstError);
        $expected = LocalizationUtility::translate('captcha.validationError', 'onetimeaccount');
        self::assertSame($expected, $firstError->getMessage());
    }

    private function createFutureDateTime(): \DateTime
    {
        $nowAsUnixTimestamp = (int)GeneralUtility::makeInstance(Context::class)
            ->getPropertyFromAspect('date', 'timestamp');
        $dateTime = new \DateTime();
        $dateTime->setTimestamp($nowAsUnixTimestamp + 1);

        return $dateTime;
    }

    /**
     * @test
     */
    public function validateWithNullAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $result = $this->subject->validate(null);

        self::assertCaptchaValidationError($result);
    }

    /**
     * @test
     */
    public function validateWithNonCaptchaObjectAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $result = $this->subject->validate(new \stdClass());

        self::assertCaptchaValidationError($result);
    }

    /**
     * @test
     */
    public function validateWithCaptchaWithoutValidUntilAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $result = $this->subject->validate(new Captcha());

        self::assertCaptchaValidationError($result);
    }

    /**
     * @test
     */
    public function validateWithFutureValidUntilAndEmptyAnswerAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $captcha = new Captcha();
        $captcha->setValidUntil($this->createFutureDateTime());
        $captcha->setGivenAnswer('');

        $result = $this->subject->validate($captcha);

        self::assertCaptchaValidationError($result);
    }

    /**
     * @test
     */
    public function validateWithFutureValidUntilAndOtherGivenAnswerAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $captcha = new Captcha();
        $captcha->setValidUntil($this->createFutureDateTime());
        $captcha->setGivenAnswer('foo');

        $result = $this->subject->validate($captcha);

        self::assertCaptchaValidationError($result);
    }

    /**
     * @test
     */
    public function validateWithFutureValidUntilAndGivenAnswerMatchingTheCorrectAnswerNotAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $captcha = new Captcha();
        $captcha->setValidUntil($this->createFutureDateTime());
        $captcha->setGivenAnswer('foo');

        $expectedCaptcha = new Captcha();
        $expectedCaptcha->setValidUntil($this->createFutureDateTime());
        $this->captchaFactory->fillCorrectAnswer($expectedCaptcha);
        $captcha->setGivenAnswer($expectedCaptcha->getCorrectAnswer());

        $result = $this->subject->validate($captcha);

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     */
    public function validateWithValidUntilRightNowAndGivenAnswerMatchingTheCorrectAnswerNotAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $nowAsUnixTimestamp = (int)GeneralUtility::makeInstance(Context::class)
            ->getPropertyFromAspect('date', 'timestamp');
        $validUntil = new \DateTime();
        $validUntil->setTimestamp($nowAsUnixTimestamp);

        $captcha = new Captcha();
        $captcha->setValidUntil($validUntil);
        $captcha->setGivenAnswer('foo');

        $expectedCaptcha = new Captcha();
        $expectedCaptcha->setValidUntil($validUntil);
        $this->captchaFactory->fillCorrectAnswer($expectedCaptcha);
        $captcha->setGivenAnswer($expectedCaptcha->getCorrectAnswer());

        $result = $this->subject->validate($captcha);

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     */
    public function validateWithPastValidUntilAndGivenAnswerMatchingTheCorrectAnswerAddsError(): void
    {
        $this->subject->setSettings(['captcha' => '1']);

        $nowAsUnixTimestamp = (int)GeneralUtility::makeInstance(Context::class)
            ->getPropertyFromAspect('date', 'timestamp');
        $validUntil = new \DateTime();
        $validUntil->setTimestamp($nowAsUnixTimestamp - 1);

        $captcha = new Captcha();
        $captcha->setValidUntil($validUntil);
        $captcha->setGivenAnswer('foo');

        $expectedCaptcha = new Captcha();
        $expectedCaptcha->setValidUntil($validUntil);
        $this->captchaFactory->fillCorrectAnswer($expectedCaptcha);
        $captcha->setGivenAnswer($expectedCaptcha->getCorrectAnswer());

        $result = $this->subject->validate($captcha);

        self::assertCaptchaValidationError($result);
    }
}
