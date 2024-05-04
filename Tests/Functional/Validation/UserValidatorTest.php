<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Validation;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Onetimeaccount\Validation\UserValidator;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Validation\UserValidator
 */
final class UserValidatorTest extends FunctionalTestCase
{
    /**
     * @var array<int, non-empty-string>
     */
    private const VALIDATABLE_FIELDS = [
        'company',
        'gender',
        'fullSalutation',
        'name',
        'firstName',
        'lastName',
        'title',
        'address',
        'zip',
        'city',
        'zone',
        'country',
        'email',
        'telephone',
        'www',
        'dateOfBirth',
        'status',
        'comments',
        'privacy',
    ];

    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/onetimeaccount',
    ];

    protected $coreExtensionsToLoad = ['extbase', 'fluid'];

    private UserValidator $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['LANG'] = $this->get(LanguageService::class);

        $this->subject = new UserValidator();
    }

    /**
     * @return array<string, array{0: non-empty-string}>
     */
    public function fieldDataProvider(): array
    {
        $dataSets = [];
        foreach (self::VALIDATABLE_FIELDS as $field) {
            $dataSets[$field] = [$field];
        }

        return $dataSets;
    }

    /**
     * @test
     *
     * @param non-empty-string $field
     *
     * @dataProvider fieldDataProvider
     */
    public function validateWithEmptyModelForSingleFieldRequiredAndShownAddsErrorForRequiredField(string $field): void
    {
        $this->subject->setSettings(['fieldsToShow' => $field, 'requiredFields' => $field]);

        $result = $this->subject->validate(new FrontendUser());

        self::assertTrue($result->hasErrors());
        $forProperty = $result->forProperty($field);
        self::assertCount(1, $forProperty->getErrors());
        $firstError = $forProperty->getFirstError();
        self::assertInstanceOf(Error::class, $firstError);
        $expected = LocalizationUtility::translate('validationError.fillInField', 'oelib');
        self::assertSame($expected, $firstError->getMessage());
    }

    /**
     * @test
     */
    public function validateModelWithEmptyModelForAllFieldsRequiredAndVisibleReturnsErrors(): void
    {
        $concatenatedFields = \implode(',', self::VALIDATABLE_FIELDS);
        $this->subject->setSettings(['fieldsToShow' => $concatenatedFields, 'requiredFields' => $concatenatedFields]);

        $result = $this->subject->validate(new FrontendUser());

        self::assertTrue($result->hasErrors());
    }
}
