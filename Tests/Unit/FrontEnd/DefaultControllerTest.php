<?php

declare(strict_types=1);

namespace OliverKlee\OneTimeAccount\Tests\Unit\FrontEnd;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\OneTimeAccount\Tests\Unit\FrontEnd\Fixtures\FakeDefaultController;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class DefaultControllerTest extends UnitTestCase
{
    /**
     * @var FakeDefaultController
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new FakeDefaultController();

        $configurationProxy = \Tx_Oelib_ConfigurationProxy::getInstance('onetimeaccount');
        $configurationProxy->setAsBoolean('enableConfigCheck', false);
        $configurationProxy->setAsBoolean('enableLogging', false);
    }

    /*
     * Tests concerning getFormData
     */

    /**
     * @test
     */
    public function getFormDataReturnsNonEmptyDataSetViaSetFormData()
    {
        $this->subject->setFormData(['foo' => 'bar']);

        self::assertSame(
            'bar',
            $this->subject->getFormData('foo')
        );
    }

    /*
     * Tests concerning createInitialUserName
     */

    /**
     * @test
     */
    public function createInitialUserNameForEmailSourceAndForNonEmptyEmailReturnsTheEmail()
    {
        $this->subject->setConfigurationValue('userNameSource', 'email');
        $this->subject->setFormData(['email' => 'foo@example.com']);

        self::assertSame(
            'foo@example.com',
            $this->subject->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForInvalidSourceAndForNonEmptyEmailReturnsTheEmail()
    {
        $this->subject->setConfigurationValue('userNameSource', 'somethingInvalid');
        $this->subject->setFormData(['email' => 'foo@example.com']);

        self::assertSame(
            'foo@example.com',
            $this->subject->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForEmailSourceAndForEmptyEmailReturnsUser()
    {
        $this->subject->setConfigurationValue('userNameSource', 'email');
        $this->subject->setFormData(['email' => '']);

        self::assertSame(
            'user',
            $this->subject->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForEmptyNameFieldsReturnsUser()
    {
        $this->subject->setConfigurationValue('userNameSource', 'name');
        $this->subject->setFormData([]);

        self::assertSame(
            'user',
            $this->subject->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForNonEmptyFullNameFieldsReturnsLowercasedFullNameWithDots()
    {
        $this->subject->setConfigurationValue('userNameSource', 'name');
        $this->subject->setFormData(['name' => 'John Doe']);

        self::assertSame(
            'john.doe',
            $this->subject->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForNonEmptyFullNameFieldsTrimsName()
    {
        $this->subject->setConfigurationValue('userNameSource', 'name');
        $this->subject->setFormData(['name' => ' John Doe ']);

        self::assertSame(
            'john.doe',
            $this->subject->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForNonEmptyFirstAndLastReturnsFirstAndLastName()
    {
        $this->subject->setConfigurationValue('userNameSource', 'name');
        $this->subject->setFormData(['first_name' => 'John', 'last_name' => 'Doe']);

        self::assertSame(
            'john.doe',
            $this->subject->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForNonEmptyFirstAndEmptyLastReturnsFirstName()
    {
        $this->subject->setConfigurationValue('userNameSource', 'name');
        $this->subject->setFormData(['first_name' => 'John', 'last_name' => '']);

        self::assertSame(
            'john',
            $this->subject->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForEmptyFirstAndNonEmptyLastReturnsLastName()
    {
        $this->subject->setConfigurationValue('userNameSource', 'name');
        $this->subject->setFormData(['first_name' => '', 'last_name' => 'Doe']);

        self::assertSame(
            'doe',
            $this->subject->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceAndForTwoPartFirstNameReturnsBothParts()
    {
        $this->subject->setConfigurationValue('userNameSource', 'name');
        $this->subject->setFormData(['first_name' => 'John Sullivan', 'last_name' => 'Doe']);

        self::assertSame(
            'john.sullivan.doe',
            $this->subject->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceDropsAmpersandAndComma()
    {
        $this->subject->setConfigurationValue('userNameSource', 'name');
        $this->subject->setFormData(['first_name' => 'Tom & Jerry', 'last_name' => 'Smith, Miller']);

        self::assertSame(
            'tom.jerry.smith.miller',
            $this->subject->createInitialUserName()
        );
    }

    /**
     * @test
     */
    public function createInitialUserNameForNameSourceDropsSpecialCharacters()
    {
        $this->subject->setConfigurationValue('userNameSource', 'name');
        $this->subject->setFormData(['first_name' => 'Sölüläß', 'last_name' => 'Smith']);

        self::assertSame(
            'sll.smith',
            $this->subject->createInitialUserName()
        );
    }

    /*
     * Tests concerning getPassword
     */

    /**
     * @test
     */
    public function getPasswordReturnsPasswordWithEightCharacters()
    {
        self::assertSame(
            8,
            \strlen($this->subject->getPassword())
        );
    }

    /*
     * Tests concerning validateStringField
     */

    /**
     * @test
     */
    public function validateStringFieldForMissingFieldNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->validateStringField([]);
    }

    /**
     * @test
     */
    public function validateStringFieldForRequiredFieldAndNonEmptyValueReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertTrue($this->subject->validateStringField(['elementName' => 'name', 'value' => 'foo']));
    }

    /**
     * @test
     */
    public function validateStringFieldRequiredFieldAndEmptyValueReturnsFalse()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertFalse($this->subject->validateStringField(['elementName' => 'name', 'value' => '']));
    }

    /**
     * @test
     */
    public function validateStringFieldRequiredFieldAndMissingValueReturnsFalse()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertFalse($this->subject->validateStringField(['elementName' => 'name']));
    }

    /**
     * @test
     */
    public function validateStringFieldForNonRequiredFieldAndNonEmptyValueReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', '');

        self::assertTrue($this->subject->validateStringField(['elementName' => 'name', 'value' => 'hello']));
    }

    /**
     * @test
     */
    public function validateStringFieldForNonRequiredFieldAndEmptyValueReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', '');

        self::assertTrue($this->subject->validateStringField(['elementName' => 'name', 'value' => '']));
    }

    /**
     * @test
     */
    public function validateStringFieldForNonRequiredFieldAndMissingValueReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', '');

        self::assertTrue($this->subject->validateStringField(['elementName' => 'name']));
    }

    /*
     * Tests concerning validateIntegerField
     */

    /**
     * @test
     */
    public function validateIntegerFieldForMissingFieldNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->validateIntegerField([]);
    }

    /**
     * @test
     */
    public function validateIntegerFieldForRequiredFieldAndPositiveValueReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertTrue(
            $this->subject->validateIntegerField(['elementName' => 'name', 'value' => 1])
        );
    }

    /**
     * @test
     */
    public function validateIntegerFieldForRequiredFieldAndStringValueReturnsFalse()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertFalse($this->subject->validateIntegerField(['elementName' => 'name', 'value' => 'foo']));
    }

    /**
     * @test
     */
    public function validateIntegerFieldForRequiredFieldAndZeroValueReturnsFalse()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertFalse($this->subject->validateIntegerField(['elementName' => 'name', 'value' => 0]));
    }

    /**
     * @test
     */
    public function validateIntegerFieldForRequiredFieldAndMissingValueReturnsFalse()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertFalse($this->subject->validateIntegerField(['elementName' => 'name']));
    }

    /**
     * @test
     */
    public function validateIntegerFieldForNonRequiredFieldAndPositiveValueReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', '');

        self::assertTrue($this->subject->validateIntegerField(['elementName' => 'name', 'value' => 12]));
    }

    /**
     * @test
     */
    public function validateIntegerFieldForNonRequiredFieldAndZeroValueReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', '');

        self::assertTrue($this->subject->validateIntegerField(['elementName' => 'name', 'value' => 0]));
    }

    /**
     * @test
     */
    public function validateIntegerFieldForNonRequiredFieldAndMissingValueReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', '');

        self::assertTrue($this->subject->validateIntegerField(['elementName' => 'name']));
    }

    /*
     * Tests concerning validateBooleanField
     */

    /**
     * @test
     */
    public function validateBooleanFieldForMissingFieldNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->validateBooleanField([]);
    }

    /**
     * @test
     */
    public function validateBooleanFieldForRequiredFieldAndOneValueReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'privacy');

        self::assertTrue(
            $this->subject->validateBooleanField(['elementName' => 'privacy', 'value' => '1'])
        );
    }

    /**
     * @test
     */
    public function validateBooleanFieldForRequiredFieldAndStringValueReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'privacy');

        self::assertTrue($this->subject->validateBooleanField(['elementName' => 'privacy', 'value' => 'foo']));
    }

    /**
     * @test
     */
    public function validateBooleanFieldForRequiredFieldAndZeroValueReturnsFalse()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'privacy');

        self::assertFalse($this->subject->validateBooleanField(['elementName' => 'privacy', 'value' => '0']));
    }

    /**
     * @test
     */
    public function validateBooleanFieldForRequiredFieldAndMissingValueReturnsFalse()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'privacy');

        self::assertFalse($this->subject->validateBooleanField(['elementName' => 'privacy']));
    }

    /**
     * @test
     */
    public function validateBooleanFieldForNonRequiredFieldAndPositiveValueReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', '');

        self::assertTrue($this->subject->validateBooleanField(['elementName' => 'privacy', 'value' => '12']));
    }

    /**
     * @test
     */
    public function validateBooleanFieldForNonRequiredFieldAndZeroValueReturnsFalse()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', '');

        self::assertTrue($this->subject->validateBooleanField(['elementName' => 'privacy', 'value' => '0']));
    }

    /**
     * @test
     */
    public function validateBooleanFieldForNonRequiredFieldAndMissingValueReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', '');

        self::assertTrue($this->subject->validateBooleanField(['elementName' => 'privacy']));
    }

    /*
     * Tests concerning getPidForNewUserRecords
     */

    /**
     * @test
     */
    public function getPidForNewUserRecordsForEmptyConfigValueReturnsZero()
    {
        $this->subject->setConfigurationValue(
            'systemFolderForNewFeUserRecords',
            ''
        );

        self::assertSame(
            0,
            $this->subject->getPidForNewUserRecords()
        );
    }

    /**
     * @test
     */
    public function getPidForNewUserRecordsForConfigValueStringReturnsZero()
    {
        $this->subject->setConfigurationValue(
            'systemFolderForNewFeUserRecords',
            'foo'
        );

        self::assertSame(
            0,
            $this->subject->getPidForNewUserRecords()
        );
    }

    /**
     * @test
     */
    public function getPidForNewUserRecordsForConfigValueIntegerReturnsInteger()
    {
        $this->subject->setConfigurationValue(
            'systemFolderForNewFeUserRecords',
            42
        );

        self::assertSame(
            42,
            $this->subject->getPidForNewUserRecords()
        );
    }

    /*
     * Tests concerning setAllNamesSubpartVisibility
     */

    /**
     * @test
     */
    public function setAllNamesSubpartVisibilityForAllNameRelatedFieldsHiddenAddsAllNamesSubpartToHideFields()
    {
        $fieldsToHide = ['name', 'gender', 'first_name', 'last_name'];
        $this->subject->setAllNamesSubpartVisibility($fieldsToHide);

        self::assertContains('all_names', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setAllNamesSubpartVisibilityForVisibleNameFieldDoesNotAddAllNamesSubpartToHideFields()
    {
        $fieldsToHide = ['gender', 'first_name', 'last_name'];
        $this->subject->setAllNamesSubpartVisibility($fieldsToHide);

        self::assertNotContains('all_names', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setAllNamesSubpartVisibilityForVisibleFirstNameFieldDoesNotAddAllNamesSubpartToHideFields()
    {
        $fieldsToHide = ['name', 'gender', 'last_name'];
        $this->subject->setAllNamesSubpartVisibility($fieldsToHide);

        self::assertNotContains('all_names', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setAllNamesSubpartVisibilityForVisibleLastNameFieldDoesNotAddAllNamesSubpartToHideFields()
    {
        $fieldsToHide = ['name', 'gender', 'first_name'];
        $this->subject->setAllNamesSubpartVisibility($fieldsToHide);

        self::assertNotContains('all_names', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setAllNamesSubpartVisibilityForVisibleGenderFieldDoesNotAddAllNamesSubpartToHideFields()
    {
        $fieldsToHide = ['name', 'first_name', 'last_name'];
        $this->subject->setAllNamesSubpartVisibility($fieldsToHide);

        self::assertNotContains('all_names', $fieldsToHide);
    }

    /*
     * Tests concerning setZipSubpartVisibility
     */

    /**
     * @test
     */
    public function setZipSubpartVisibilityForHiddenCityAndZipAddsZipOnlySubpartToHideFields()
    {
        $fieldsToHide = ['zip', 'city'];
        $this->subject->setZipSubpartVisibility($fieldsToHide);

        self::assertContains('zip_only', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setZipSubpartVisibilityForShownCityAndZipAddsZipOnlySubpartToHideFields()
    {
        $fieldsToHide = [];
        $this->subject->setZipSubpartVisibility($fieldsToHide);

        self::assertContains('zip_only', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setZipSubpartVisibilityForShownCityAndHiddenZipAddsZipOnlySubpartToHideFields()
    {
        $fieldsToHide = ['zip'];
        $this->subject->setZipSubpartVisibility($fieldsToHide);

        self::assertContains('zip_only', $fieldsToHide);
    }

    /**
     * @test
     */
    public function setZipSubpartVisibilityForHiddenCityAndShownZipDoesNotAddZipOnlySubpartToHideFields()
    {
        $fieldsToHide = ['city'];
        $this->subject->setZipSubpartVisibility($fieldsToHide);

        self::assertNotContains('zip_only', $fieldsToHide);
    }

    /*
     * Tests concerning preprocessFormData
     */

    /**
     * @test
     */
    public function preprocessFormDataForNameHiddenUsesFirstNameAndLastNameAsName()
    {
        $this->subject->setConfigurationValue(
            'feUserFieldsToDisplay',
            'first_name, last_name'
        );
        $this->subject->setFormFieldsToShow();

        $formData = $this->subject->preprocessFormData([
            'first_name' => 'foo',
            'last_name' => 'bar',
        ]);

        self::assertSame(
            'foo bar',
            $formData['name']
        );
    }

    /**
     * @test
     */
    public function preprocessFormDataForShownNameFieldUsesValueOfNameField()
    {
        $this->subject->setConfigurationValue(
            'feUserFieldsToDisplay',
            'name,first_name,last_name'
        );
        $this->subject->setFormFieldsToShow();

        $formData = $this->subject->preprocessFormData([
            'name' => 'foobar',
            'first_name' => 'foo',
            'last_name' => 'bar',
        ]);

        self::assertSame(
            'foobar',
            $formData['name']
        );
    }

    /**
     * @test
     */
    public function preprocessFormDataForUserGroupSetInConfigurationSetsTheUsersGroupInFormData()
    {
        $userGroupUid = 1;
        $this->subject->setConfigurationValue(
            'feUserFieldsToDisplay',
            'name'
        );
        $this->subject->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid
        );
        $this->subject->setFormFieldsToShow();

        $formData = $this->subject->preprocessFormData(['name' => 'bar']);

        self::assertSame(
            (string)$userGroupUid,
            $formData['usergroup']
        );
    }

    /**
     * @test
     */
    public function preprocessFormDataForTwoUserGroupsInConfigurationAndOneInFormSetsTheSelectedUserGroupInFormData()
    {
        $userGroupUid = 1;
        $userGroupUid2 = 2;
        $this->subject->setConfigurationValue(
            'feUserFieldsToDisplay',
            'name,usergroups'
        );
        $this->subject->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid . ',' . $userGroupUid2
        );
        $this->subject->setFormFieldsToShow();

        $formData = $this->subject->preprocessFormData(
            ['usergroup' => $userGroupUid]
        );

        self::assertSame(
            $userGroupUid,
            $formData['usergroup']
        );
    }

    /**
     * @test
     */
    public function preprocessFormDataForTwoUserGroupsInConfigurationTheGroupFieldHiddenSetsGroupsFromConfiguration()
    {
        $userGroupUid = 1;
        $userGroupUid2 = 2;
        $this->subject->setConfigurationValue(
            'feUserFieldsToDisplay',
            'name'
        );
        $this->subject->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid . ',' . $userGroupUid2
        );
        $this->subject->setFormFieldsToShow();

        $formData = $this->subject->preprocessFormData([]);

        self::assertSame(
            $userGroupUid . ',' . $userGroupUid2,
            $formData['usergroup']
        );
    }

    /**
     * @test
     */
    public function preprocessFormDataForUserGroupInConfigurationButNoGroupInFormSetsTheUsersGroupFromConfiguration()
    {
        $userGroupUid = 1;
        $this->subject->setConfigurationValue(
            'feUserFieldsToDisplay',
            'name,usergroup'
        );
        $this->subject->setConfigurationValue(
            'groupForNewFeUsers',
            $userGroupUid
        );
        $this->subject->setFormFieldsToShow();

        $formData = $this->subject->preprocessFormData(
            ['name' => 'bar', 'usergroup' => '']
        );

        self::assertSame(
            (string)$userGroupUid,
            $formData['usergroup']
        );
    }

    /**
     * @return string[][]
     */
    public function nameFieldsDataProvider(): array
    {
        return [
            'name only' => ['name'],
            'first name only' => ['first_name'],
            'last name only' => ['last_name'],
            'first and last name' => ['first_name, last_name'],
            'name and email' => ['name, email'],
        ];
    }

    /**
     * @test
     *
     * @param string $enabledFields
     *
     * @dataProvider nameFieldsDataProvider
     */
    public function isAnyNameFieldEnabledForAnyNameFieldEnabledReturnsTrue($enabledFields)
    {
        $this->subject->setConfigurationValue('feUserFieldsToDisplay', $enabledFields);
        $this->subject->setFormFieldsToShow();

        $result = $this->subject->isAnyNameFieldEnabled();

        self::assertTrue($result);
    }

    /**
     * @test
     *
     * @dataProvider nameFieldsDataProvider
     */
    public function isAnyNameFieldEnabledForOnlyNoNameFieldEnabledReturnsFalse()
    {
        $this->subject->setConfigurationValue('feUserFieldsToDisplay', 'email, city');
        $this->subject->setFormFieldsToShow();

        $result = $this->subject->isAnyNameFieldEnabled();

        self::assertFalse($result);
    }

    /**
     * @test
     */
    public function getUidOfFirstUserGroupForOneGroupReturnsThatGroupUid()
    {
        $groupUid = 42;
        $this->subject->setConfigurationValue('groupForNewFeUsers', (string)$groupUid);

        $result = $this->subject->getUidOfFirstUserGroup();

        self::assertSame($groupUid, $result);
    }

    /**
     * @test
     */
    public function getUidOfFirstUserGroupForTwoGroupsReturnsFirstGroupUid()
    {
        $this->subject->setConfigurationValue('groupForNewFeUsers', '1,2');

        $result = $this->subject->getUidOfFirstUserGroup();

        self::assertSame(1, $result);
    }

    /**
     * @test
     */
    public function getUidOfFirstUserGroupForNoGroupReturnsZero()
    {
        $this->subject->setConfigurationValue('groupForNewFeUsers', '');

        $result = $this->subject->getUidOfFirstUserGroup();

        self::assertSame(0, $result);
    }
}
