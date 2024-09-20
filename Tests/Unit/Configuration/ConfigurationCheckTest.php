<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Configuration;

use OliverKlee\Oelib\Configuration\AbstractConfigurationCheck;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Onetimeaccount\Configuration\ConfigurationCheck;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Configuration\ConfigurationCheck
 */
final class ConfigurationCheckTest extends UnitTestCase
{
    private ConfigurationCheck $subject;

    private DummyConfiguration $configuration;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configuration = new DummyConfiguration();
        $this->subject = new ConfigurationCheck($this->configuration, 'plugin.tx_onetimeaccount.settings');
    }

    /**
     * @test
     */
    public function isConfigurationCheck(): void
    {
        self::assertInstanceOf(AbstractConfigurationCheck::class, $this->subject);
    }

    /**
     * @test
     */
    public function checkWithConfigurationWithErrorsCreatesErrors(): void
    {
        $this->configuration->setAllData(['fieldsToShow' => 'bar', 'requiredFields' => 'foo']);

        $this->subject->check();

        $result = $this->subject->getWarningsAsHtml();
        self::assertNotSame([], $result);
    }

    /**
     * @test
     */
    public function checkWithWithConfigurationWithErrorsUsesProvidedNamespaceForErrors(): void
    {
        $this->configuration->setAllData(['fieldsToShow' => 'bar', 'requiredFields' => 'foo']);

        $this->subject->check();

        $result = $this->subject->getWarningsAsHtml();
        self::assertNotSame([], $result);
        self::assertArrayHasKey(0, $result);
        self::assertStringContainsString('plugin.tx_onetimeaccount.settings', $result[0]);
    }
}
