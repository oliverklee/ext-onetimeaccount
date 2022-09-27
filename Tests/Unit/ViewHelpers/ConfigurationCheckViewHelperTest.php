<?php

declare(strict_types=1);

namespace OliverKlee\Oelib\Tests\Unit\ViewHelpers;

use OliverKlee\Oelib\Configuration\ConfigurationProxy;
use OliverKlee\Oelib\Configuration\DummyConfiguration;
use OliverKlee\Oelib\ViewHelpers\AbstractConfigurationCheckViewHelper;
use OliverKlee\Oelib\ViewHelpers\IsFieldEnabledViewHelper;
use OliverKlee\Onetimeaccount\ViewHelpers\ConfigurationCheckViewHelper;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\Variables\VariableProviderInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;

/**
 * @covers \OliverKlee\Onetimeaccount\ViewHelpers\ConfigurationCheckViewHelper
 */
final class ConfigurationCheckViewHelperTest extends UnitTestCase
{
    /**
     * @var \Closure
     */
    private $renderChildrenClosure;

    /**
     * @var RenderingContextInterface
     */
    private $renderingContext;

    /**
     * @var ObjectProphecy<VariableProviderInterface>
     */
    private $variableProviderProphecy;

    /**
     * @var VariableProviderInterface
     */
    private $variableProvider;

    protected function setUp(): void
    {
        parent::setUp();

        $this->renderChildrenClosure = static function (): string {
            return '';
        };
        $renderingContextProphecy = $this->prophesize(RenderingContextInterface::class);
        $this->renderingContext = $renderingContextProphecy->reveal();
        $this->variableProviderProphecy = $this->prophesize(VariableProviderInterface::class);
        $this->variableProvider = $this->variableProviderProphecy->reveal();
        $renderingContextProphecy->getVariableProvider()->willReturn($this->variableProvider);
    }

    protected function tearDown(): void
    {
        ConfigurationProxy::purgeInstances();
        unset($GLOBALS['BE_USER']);

        parent::tearDown();
    }

    /**
     * @test
     */
    public function isViewHelper(): void
    {
        $subject = new ConfigurationCheckViewHelper();
        $subject->initializeArguments();

        self::assertInstanceOf(AbstractViewHelper::class, $subject);
        self::assertInstanceOf(AbstractConfigurationCheckViewHelper::class, $subject);
    }

    /**
     * @test
     */
    public function escapesChildren(): void
    {
        $subject = new IsFieldEnabledViewHelper();

        self::assertTrue($subject->isChildrenEscapingEnabled());
    }

    /**
     * @test
     */
    public function doesNotEscapeOutput(): void
    {
        $subject = new IsFieldEnabledViewHelper();

        self::assertFalse($subject->isOutputEscapingEnabled());
    }

    /**
     * @test
     */
    public function renderStaticForConfigurationCheckDisabledReturnsEmptyString(): void
    {
        $extensionKey = 'onetimeaccount';
        $extensionConfiguration = new DummyConfiguration(['enableConfigCheck' => false]);
        ConfigurationProxy::setInstance($extensionKey, $extensionConfiguration);

        /** @var ObjectProphecy<BackendUserAuthentication> $adminUserProphecy */
        $adminUserProphecy = $this->prophesize(BackendUserAuthentication::class);
        $adminUserProphecy->isAdmin()->willReturn(true);
        $GLOBALS['BE_USER'] = $adminUserProphecy->reveal();

        $result = ConfigurationCheckViewHelper::renderStatic(
            [],
            $this->renderChildrenClosure,
            $this->renderingContext
        );

        self::assertSame('', $result);
    }

    /**
     * @test
     */
    public function renderStaticForMissingSettingsInArgumentsThrowsException(): void
    {
        $this->expectExceptionCode(\UnexpectedValueException::class);
        $this->expectExceptionMessage('No settings in the variable container found.');
        $this->expectExceptionCode(1651153736);

        $this->variableProviderProphecy->get('settings')->willReturn(null);

        $extensionKey = 'onetimeaccount';
        $extensionConfiguration = new DummyConfiguration(['enableConfigCheck' => true]);
        ConfigurationProxy::setInstance($extensionKey, $extensionConfiguration);

        /** @var ObjectProphecy<BackendUserAuthentication> $adminUserProphecy */
        $adminUserProphecy = $this->prophesize(BackendUserAuthentication::class);
        $adminUserProphecy->isAdmin()->willReturn(true);
        $GLOBALS['BE_USER'] = $adminUserProphecy->reveal();

        $result = ConfigurationCheckViewHelper::renderStatic(
            [],
            $this->renderChildrenClosure,
            $this->renderingContext
        );

        self::assertSame('This is a configuration check warning.', $result);
    }

    /**
     * @test
     */
    public function renderStaticForConfigurationCheckEnabledWithErrorsReturnsMessageFromConfigurationCheck(): void
    {
        $extensionKey = 'onetimeaccount';
        $extensionConfiguration = new DummyConfiguration(['enableConfigCheck' => true]);
        ConfigurationProxy::setInstance($extensionKey, $extensionConfiguration);
        $this->variableProviderProphecy->get('settings')
            ->willReturn(['fieldsToShow' => 'bar', 'requiredFields' => 'foo']);

        /** @var ObjectProphecy<BackendUserAuthentication> $adminUserProphecy */
        $adminUserProphecy = $this->prophesize(BackendUserAuthentication::class);
        $adminUserProphecy->isAdmin()->willReturn(true);
        $GLOBALS['BE_USER'] = $adminUserProphecy->reveal();

        $result = ConfigurationCheckViewHelper::renderStatic(
            [],
            $this->renderChildrenClosure,
            $this->renderingContext
        );

        self::assertStringContainsString('plugin.tx_onetimeaccount.settings.requiredFields', $result);
    }
}
