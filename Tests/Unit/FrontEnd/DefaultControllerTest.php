<?php

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
            strlen($this->subject->getPassword())
        );
    }

    /*
     * Tests concerning validateStringField
     */

    /**
     * @test
     */
    public function validateStringFieldForNotRequiredFieldReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertTrue(
            $this->subject->validateStringField(
                ['elementName' => 'address']
            )
        );
    }

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
    public function validateStringFieldForNonEmptyRequiredFieldReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertTrue(
            $this->subject->validateStringField(
                ['elementName' => 'name', 'value' => 'foo']
            )
        );
    }

    /**
     * @test
     */
    public function validateStringFieldForEmptyRequiredFieldReturnsFalse()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertFalse(
            $this->subject->validateStringField(
                ['elementName' => 'name', 'value' => '']
            )
        );
    }

    /*
     * Tests concerning validateIntegerField
     */

    /**
     * @test
     */
    public function validateIntegerFieldForRequiredFieldValueZeroReturnsFalse()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertFalse(
            $this->subject->validateIntegerField(
                ['elementName' => 'name', 'value' => 0]
            )
        );
    }

    /**
     * @test
     */
    public function validateIntegerFieldForNonRequiredFieldReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertTrue(
            $this->subject->validateIntegerField(
                ['elementName' => 'address']
            )
        );
    }

    /**
     * @test
     */
    public function validateIntegerFieldForRequiredFieldValueNonZeroReturnsTrue()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertTrue(
            $this->subject->validateIntegerField(
                ['elementName' => 'name', 'value' => 1]
            )
        );
    }

    /**
     * @test
     */
    public function validateIntegerFieldForRequiredFieldValueStringReturnsFalse()
    {
        $this->subject->setConfigurationValue('requiredFeUserFields', 'name');

        self::assertFalse(
            $this->subject->validateIntegerField(
                ['elementName' => 'name', 'value' => 'foo']
            )
        );
    }

    /**
     * @test
     */
    public function validateIntegerFieldForMissingFieldNameThrowsException()
    {
        $this->expectException(\InvalidArgumentException::class);

        $this->subject->validateIntegerField([]);
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
    public function preprocessFormDataForTwoUserGroupsSetInConfigurationAndOneSelectedInFormSetsTheSelectedUserGroupInFormData(
    ) {
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
    public function preprocessFormDataForTwoUserGroupsSetInConfigurationTheGroupFieldHiddenSetsTheUserGroupsFromConfiguration(
    ) {
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
    public function preprocessFormDataForUserGroupSetInConfigurationButNoGroupChosenInFormSetsTheUsersGroupFromConfiguration(
    ) {
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
}
