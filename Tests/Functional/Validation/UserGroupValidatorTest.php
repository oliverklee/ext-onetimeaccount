<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Validation;

use OliverKlee\Onetimeaccount\Validation\UserGroupValidator;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Validation\UserValidator
 */
final class UserGroupValidatorTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/onetimeaccount',
    ];

    protected $coreExtensionsToLoad = ['extbase', 'fluid'];

    /**
     * @var UserGroupValidator
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['LANG'] = $this->get(LanguageService::class);

        $this->subject = new UserGroupValidator();
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
    public function validateWithEmptyValueForFieldRequiredAndVisibleFieldsReturnsError($value): void
    {
        $this->subject->setSettings(
            [
                'groupsForNewUsers' => '42',
                'fieldsToShow' => 'userGroup',
                'requiredFields' => 'userGroup',
            ]
        );

        $result = $this->subject->validate($value);

        self::assertTrue($result->hasErrors());
        self::assertCount(1, $result->getErrors());
        $firstError = $result->getFirstError();
        self::assertInstanceOf(Error::class, $firstError);
        $expected = LocalizationUtility::translate('validationError.fillInField', 'oelib');
        self::assertSame($expected, $firstError->getMessage());
    }

    /**
     * @test
     */
    public function validateWithNotAllowedValueForFieldRequiredAndVisibleFieldsReturnsError(): void
    {
        $this->subject->setSettings(
            [
                'groupsForNewUsers' => '1,2',
                'fieldsToShow' => 'userGroup',
                'requiredFields' => 'userGroup',
            ]
        );

        $result = $this->subject->validate(15);

        self::assertTrue($result->hasErrors());
        self::assertCount(1, $result->getErrors());
        $firstError = $result->getFirstError();
        self::assertInstanceOf(Error::class, $firstError);
        $expected = LocalizationUtility::translate('validationError.fillInField', 'oelib');
        self::assertSame($expected, $firstError->getMessage());
    }
}
