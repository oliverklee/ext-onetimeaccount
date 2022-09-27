<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Configuration;

use OliverKlee\Onetimeaccount\Configuration\FlexForms;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Configuration\FlexForms
 */
final class FlexFormsTest extends UnitTestCase
{
    /**
     * @var non-empty-string
     */
    private const LOCALLANG_PREFIX = 'LLL:EXT:onetimeaccount/Resources/Private/Language/locallang.xlf:';

    /**
     * @var FlexForms
     */
    private $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new FlexForms();
    }

    /**
     * @return array<string, array<int, non-empty-string>>
     */
    public function fieldKeysDataProvider(): array
    {
        return [
            'company' => ['company'],
            'gender' => ['gender'],
            'fullSalutation' => ['fullSalutation'],
            'name' => ['name'],
            'firstName' => ['firstName'],
            'lastName' => ['lastName'],
            'title' => ['title'],
            'address' => ['address'],
            'zip' => ['zip'],
            'city' => ['city'],
            'zone' => ['zone'],
            'country' => ['country'],
            'email' => ['email'],
            'telephone' => ['telephone'],
            'www' => ['www'],
            'dateOfBirth' => ['dateOfBirth'],
            'status' => ['status'],
            'comments' => ['comments'],
            'privacy' => ['privacy'],
        ];
    }

    /**
     * @test
     *
     * @param non-empty-string $fieldKey
     *
     * @dataProvider fieldKeysDataProvider
     */
    public function buildFieldsCreatesArrayWithLabelsAndFieldKeys(string $fieldKey): void
    {
        $configuration = [];
        $this->subject->buildFields($configuration);

        self::assertArrayHasKey('items', $configuration);
        $items = $configuration['items'];
        self::assertIsArray($items);
        $expected = [self::LOCALLANG_PREFIX . $fieldKey, $fieldKey];
        self::assertContains($expected, $items);
    }
}
