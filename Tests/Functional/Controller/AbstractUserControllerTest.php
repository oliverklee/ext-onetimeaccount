<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Controller;

use OliverKlee\FeUserExtraFields\Domain\Model\FrontendUser;
use OliverKlee\Onetimeaccount\Controller\AbstractUserController;
use OliverKlee\Onetimeaccount\Tests\Functional\Controller\Fixtures\XclassFrontendUser;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * Note: This test should be a unit test, but `GeneralUtility::flushInternalRuntimeCaches` currently cannot flush
 * the class name cache yet.
 *
 * @template C of AbstractUserController
 */
abstract class AbstractUserControllerTest extends FunctionalTestCase
{
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/onetimeaccount',
    ];

    protected $coreExtensionsToLoad = ['extbase', 'fluid'];

    protected $initializeDatabase = false;

    /**
     * @var C&MockObject&AccessibleObjectInterface
     */
    protected $subject;

    /**
     * @var ObjectProphecy<TemplateView>
     */
    private $viewProphecy;

    protected function setUpAndInjectSharedDependencies(): void
    {
        $this->viewProphecy = $this->prophesize(TemplateView::class);
        $view = $this->viewProphecy->reveal();
        $this->subject->_set('view', $view);
    }

    protected function tearDown(): void
    {
        // @phpstan-ignore-next-line We know that the necessary array keys exist.
        unset($GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FrontendUser::class]);
        $this->resetRequestData();
        parent::tearDown();
    }

    protected function setDummyRequestData(): void
    {
        $this->resetRequestData();
    }

    private function resetRequestData(): void
    {
        $_GET = [];
        $_POST = [];
    }

    /**
     * @test
     */
    public function newActionWithoutUserPassesCanPassVirginSubclassedUserToView(): void
    {
        // @phpstan-ignore-next-line We know that the necessary array keys exist.
        $GLOBALS['TYPO3_CONF_VARS']['SYS']['Objects'][FrontendUser::class] = ['className' => XclassFrontendUser::class];

        $this->viewProphecy->assign('user', Argument::type(XclassFrontendUser::class))->shouldBeCalled();

        $this->subject->newAction();
    }
}
