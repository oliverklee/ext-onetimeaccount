<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Domain\Model;

use OliverKlee\Onetimeaccount\Domain\Model\Captcha;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Domain\Model\Captcha
 */
final class CaptchaTest extends UnitTestCase
{
    private Captcha $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $this->subject = new Captcha();
    }

    /**
     * @test
     */
    public function isAbstractEntity(): void
    {
        self::assertInstanceOf(AbstractEntity::class, $this->subject);
    }

    /**
     * @test
     */
    public function getValidUntilInitiallyReturnsNull(): void
    {
        self::assertNull($this->subject->getValidUntil());
    }

    /**
     * @test
     */
    public function setValidUntilSetsValidUntil(): void
    {
        $validUntil = new \DateTime();
        $this->subject->setValidUntil($validUntil);

        self::assertSame($validUntil, $this->subject->getValidUntil());
    }

    /**
     * @test
     */
    public function setValidUntilCanSetValidUntilToNull(): void
    {
        $this->subject->setValidUntil(null);

        self::assertNull($this->subject->getValidUntil());
    }

    /**
     * @test
     */
    public function getCorrectAnswerInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getCorrectAnswer());
    }

    /**
     * @test
     */
    public function setCorrectAnswerSetsCorrectAnswer(): void
    {
        $value = 'Club-Mate';
        $this->subject->setCorrectAnswer($value);

        self::assertSame($value, $this->subject->getCorrectAnswer());
    }

    /**
     * @test
     */
    public function getDecoyAnswerInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getDecoyAnswer());
    }

    /**
     * @test
     */
    public function setDecoyAnswerSetsDecoyAnswer(): void
    {
        $value = 'Club-Mate';
        $this->subject->setDecoyAnswer($value);

        self::assertSame($value, $this->subject->getDecoyAnswer());
    }

    /**
     * @test
     */
    public function getGivenAnswerInitiallyReturnsEmptyString(): void
    {
        self::assertSame('', $this->subject->getGivenAnswer());
    }

    /**
     * @test
     */
    public function setGivenAnswerSetsGivenAnswer(): void
    {
        $value = 'Club-Mate';
        $this->subject->setGivenAnswer($value);

        self::assertSame($value, $this->subject->getGivenAnswer());
    }
}
