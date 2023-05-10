<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Service;

use OliverKlee\Onetimeaccount\Domain\Model\Captcha;
use OliverKlee\Onetimeaccount\Service\CaptchaFactory;
use PHPUnit\Framework\MockObject\MockObject;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Service\CaptchaFactory
 */
final class CaptchaFactoryTest extends UnitTestCase
{
    /**
     * @var non-empty-string
     */
    private const DATE_FORMAT = 'Y-m-d H:i:s';

    /**
     * @var non-empty-string
     */
    private const ENCRYPTION_KEY = '$argon2i$v=19$m=65536,t=16,' .
    'p=1$dXBmSUYva2EzT2hZZEVEUA$1JtKq8v7WusoVuZ9z8BuIPP0tw03gV9CwySkaZE+DX0';

    /**
     * @var non-empty-string
     */
    private const ADDITIONAL_SECRET = 'onetimeaccount-captcha';

    /**
     * @var Context&MockObject
     */
    private $contextMock;

    /**
     * @var CaptchaFactory
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey'] = self::ENCRYPTION_KEY;

        $this->contextMock = $this->createMock(Context::class);
        GeneralUtility::setSingletonInstance(Context::class, $this->contextMock);

        $this->subject = new CaptchaFactory();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['encryptionKey']);
        GeneralUtility::purgeInstances();

        parent::tearDown();
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
    public function generateChallengeGeneratesCaptcha(): void
    {
        $result = $this->subject->generateChallenge();

        self::assertInstanceOf(Captcha::class, $result);
    }

    /**
     * @test
     */
    public function generateChallengeSetsValidUntilExactlyFiveMinutesInTheFuture(): void
    {
        $nowAsUnitTimestamp = 1671731248;

        $this->contextMock->expects(self::once())->method('getPropertyFromAspect')->with('date', 'timestamp')
            ->willReturn($nowAsUnitTimestamp);

        $result = $this->subject->generateChallenge();
        $validUntil = $result->getValidUntil();

        self::assertInstanceOf(\DateTime::class, $validUntil);
        self::assertSame($nowAsUnitTimestamp + 60 * 5, $validUntil->getTimestamp());
    }

    /**
     * @test
     */
    public function generateChallengeSetsCorrectAnswerToFortyCharacterHexString(): void
    {
        $this->contextMock->expects(self::once())->method('getPropertyFromAspect')->with('date', 'timestamp')
            ->willReturn(1671732034);

        $result = $this->subject->generateChallenge();

        self::assertRegExp('/^[\\da-f]{40}$/', $result->getCorrectAnswer());
    }

    /**
     * @test
     */
    public function generateChallengeSetsCorrectAnswerAsHashFromFormattedValidUntilDateWithEncryptionKey(): void
    {
        $nowAsUnitTimestamp = 1671731248;

        $this->contextMock->expects(self::once())->method('getPropertyFromAspect')->with('date', 'timestamp')
            ->willReturn($nowAsUnitTimestamp);

        $result = $this->subject->generateChallenge();

        $validUntil = $result->getValidUntil();
        self::assertInstanceOf(\DateTime::class, $validUntil);
        $validUntilAsString = $validUntil->format(self::DATE_FORMAT);
        $expectedAnswer = GeneralUtility::hmac($validUntilAsString, self::ADDITIONAL_SECRET);

        self::assertSame($expectedAnswer, $result->getCorrectAnswer());
    }

    /**
     * @test
     */
    public function generateChallengeSetsDecoyAnswerToFortyFourCharacterHexString(): void
    {
        $result = $this->subject->generateChallenge();

        self::assertRegExp('/^[\\da-f]{40}$/', $result->getDecoyAnswer());
    }

    /**
     * @test
     */
    public function generateChallengeSetsDecoyAnswerDifferentFromCorrectAnswer(): void
    {
        $result = $this->subject->generateChallenge();

        self::assertNotSame($result->getCorrectAnswer(), $result->getDecoyAnswer());
    }

    /**
     * @test
     */
    public function fillCorrectAnswerForCaptchaWithoutValiUntilKeepsCorrectAnswerEmpty(): void
    {
        $captcha = new Captcha();

        $this->subject->fillCorrectAnswer($captcha);

        self::assertSame('', $captcha->getCorrectAnswer());
    }

    /**
     * @test
     */
    public function fillCorrectAnswerForCaptchaWithValiUntilSetsCorrectAnswerToFortyCharactersHexString(): void
    {
        $captcha = new Captcha();
        $captcha->setValidUntil(new \DateTime());

        $this->subject->fillCorrectAnswer($captcha);

        self::assertRegExp('/^[\\da-f]{40}$/', $captcha->getCorrectAnswer());
    }

    /**
     * @test
     */
    public function fillCorrectAnswerForCaptchaWithValiUntilSetsCorrectAnswerToHashOfValidUntilAndSecrets(): void
    {
        $captcha = new Captcha();
        $validUntil = new \DateTime();
        $captcha->setValidUntil($validUntil);

        $this->subject->fillCorrectAnswer($captcha);

        $validUntilAsString = $validUntil->format(self::DATE_FORMAT);
        $expectedAnswer = GeneralUtility::hmac($validUntilAsString, self::ADDITIONAL_SECRET);
        self::assertSame($expectedAnswer, $captcha->getCorrectAnswer());
    }
}
