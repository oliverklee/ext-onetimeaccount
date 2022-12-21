<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Domain\Model;

use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

/**
 * A mathematical CAPTCHA.
 */
class Captcha extends AbstractEntity
{
    /**
     * @var \DateTime|null
     */
    protected $validUntil;

    /**
     * @var string
     */
    protected $correctAnswer = '';

    /**
     * @var string
     */
    protected $decoyAnswer = '';

    /**
     * @var string
     */
    protected $givenAnswer = '';

    public function getValidUntil(): ?\DateTime
    {
        return $this->validUntil;
    }

    public function setValidUntil(?\DateTime $validUntil): void
    {
        $this->validUntil = $validUntil;
    }

    public function getCorrectAnswer(): string
    {
        return $this->correctAnswer;
    }

    public function setCorrectAnswer(string $correctAnswer): void
    {
        $this->correctAnswer = $correctAnswer;
    }

    public function getDecoyAnswer(): string
    {
        return $this->decoyAnswer;
    }

    public function setDecoyAnswer(string $decoyAnswer): void
    {
        $this->decoyAnswer = $decoyAnswer;
    }

    public function getGivenAnswer(): string
    {
        return $this->givenAnswer;
    }

    public function setGivenAnswer(string $givenAnswer): void
    {
        $this->givenAnswer = $givenAnswer;
    }
}
