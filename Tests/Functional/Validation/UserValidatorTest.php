<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Validation;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Onetimeaccount\Validation\UserValidator;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Extbase\Validation\Error;
use TYPO3\CMS\Extbase\Validation\Validator\AbstractValidator;
use TYPO3\CMS\Extbase\Validation\Validator\ValidatorInterface;
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

    /**
     * @var UserValidator
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();
        $GLOBALS['LANG'] = $this->get(LanguageService::class);

        $this->subject = new UserValidator();
    }

    private function buildFullModel(): FrontendUser
    {
        $user = new FrontendUser();
        $user->setName('Ben Best');
        $user->setFirstName('Ben');
        $user->setLastName('Best');
        $user->setAddress('At the multi-core machine 3');
        $user->setTelephone('+49 1111 2222222');
        $user->setEmail('ben@example.com');
        $user->setTitle('developer');
        $user->setZip('12345');
        $user->setCity('Development Hill');
        $user->setCountry('Buthan');
        $user->setWww('https://example.com');
        $user->setCompany('TYPO3 Community');
        $user->setZone('ABC');
        $user->setPrivacy(true);
        $user->setFullSalutation('Yo Ben!');
        $user->setGender(FrontendUser::GENDER_MALE);
        $user->setStatus(FrontendUser::STATUS_STUDENT);
        $user->setComments('Wonderful!');
        $user->setDateOfBirth(new \DateTime('now'));

        return $user;
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
    public function validateWithNonUserValueReturnsNoErrors(): void
    {
        $result = $this->subject->validate(new \stdClass());

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     */
    public function validateWithEmptyUserForNoShowOrRequiredFieldsSettingReturnsNoErrors(): void
    {
        $result = $this->subject->validate(new FrontendUser());

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     */
    public function validateWithEmptyUserForEmptyRequiredAndVisibleFieldsReturnsNoErrors(): void
    {
        $this->subject->setSettings(['fieldsToShow' => '', 'requiredFields' => '']);

        $result = $this->subject->validate(new FrontendUser());

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     */
    public function validateWithEmptyUserForNoRequiredFieldsButAllVisibleReturnsNoErrors(): void
    {
        $concatenatedFields = \implode(',', self::VALIDATABLE_FIELDS);
        $this->subject->setSettings(['fieldsToShow' => $concatenatedFields, 'requiredFields' => '']);

        $result = $this->subject->validate(new FrontendUser());

        self::assertFalse($result->hasErrors());
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

    /**
     * @test
     */
    public function validateModelWithFullModelForAllFieldsRequiredAndVisibleReturnsNoErrors(): void
    {
        $concatenatedFields = \implode(',', self::VALIDATABLE_FIELDS);
        $this->subject->setSettings(['fieldsToShow' => $concatenatedFields, 'requiredFields' => $concatenatedFields]);

        $result = $this->subject->validate($this->buildFullModel());

        self::assertFalse($result->hasErrors());
    }

    /**
     * @test
     */
    public function validateModelWithFullModelForNoFieldsRequiredAndAllVisibleReturnsNoErrors(): void
    {
        $concatenatedFields = \implode(',', self::VALIDATABLE_FIELDS);
        $this->subject->setSettings(['fieldsToShow' => $concatenatedFields, 'requiredFields' => '']);

        $result = $this->subject->validate($this->buildFullModel());

        self::assertFalse($result->hasErrors());
    }
}
