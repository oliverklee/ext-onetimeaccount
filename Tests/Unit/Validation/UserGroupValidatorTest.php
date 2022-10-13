<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Validation;

use OliverKlee\Onetimeaccount\Validation\UserGroupValidator;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Validation\UserGroupValidator
 */
final class UserGroupValidatorTest extends UnitTestCase
{
    /**
     * @var UserGroupValidator
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new UserGroupValidator();
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
     * @return array<string, array{0: int|null}>
     */
    public function emptyDataProvider(): array
    {
        return [
            'zero' => [0],
            'null' => [null],
        ];
    }

    /**
     * @test
     *
     * @param int|null $value
     *
     * @dataProvider emptyDataProvider
     */
    public function validateWithEmptyValueForEmptyRequiredAndVisibleFieldsReturnsNoErrors($value): void
    {
        $this->subject->setSettings(['fieldsToShow' => '', 'requiredFields' => '']);

        $result = $this->subject->validate($value);

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     *
     * @param int|null $value
     *
     * @dataProvider emptyDataProvider
     */
    public function validateWithEmptyValueForFieldRequiredButNotVisibleFieldsReturnsNoErrors($value): void
    {
        $this->subject->setSettings(['fieldsToShow' => '', 'requiredFields' => 'userGroup']);

        $result = $this->subject->validate($value);

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     *
     * @param int|null $value
     *
     * @dataProvider emptyDataProvider
     */
    public function validateWithEmptyValueForFieldNotRequiredButVisibleFieldsReturnsNoErrors($value): void
    {
        $this->subject->setSettings(['fieldsToShow' => 'userGroup', 'requiredFields' => '']);

        $result = $this->subject->validate($value);

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     */
    public function validateWithValueInConfiguredGroupsForFieldRequiredAndVisibleFieldsReturnsNoErrors(): void
    {
        $userGroupUid = 42;
        $this->subject->setSettings(
            [
                'groupsForNewUsers' => $userGroupUid . ',15',
                'fieldsToShow' => 'userGroup',
                'requiredFields' => 'userGroup',
            ]
        );

        $result = $this->subject->validate($userGroupUid);

        self::assertFalse($result->hasErrors());
    }
}
