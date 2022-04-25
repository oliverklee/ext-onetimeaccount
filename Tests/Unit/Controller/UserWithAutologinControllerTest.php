<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Controller;

use OliverKlee\Onetimeaccount\Controller\UserWithAutologinController;
use PHPUnit\Framework\MockObject\MockObject;
use Prophecy\Prophecy\ObjectProphecy;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Fluid\View\TemplateView;
use TYPO3\TestingFramework\Core\AccessibleObjectInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Controller\UserWithAutologinController
 * @covers \OliverKlee\Onetimeaccount\Controller\AbstractUserController
 */
class UserWithAutologinControllerTest extends UnitTestCase
{
    /**
     * @var UserWithAutologinController&MockObject&AccessibleObjectInterface
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $subject;

    /**
     * @var ObjectProphecy<TemplateView>
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $viewProphecy;

    protected function setUp(): void
    {
        parent::setUp();

        // We need to create an accessible mock in order to be able to set the protected `view`.
        // We can drop the additional arguments to skip the original constructor once we drop support for TYPO3 V9.
        $this->subject
            = $this->getAccessibleMock(UserWithAutologinController::class, ['redirect', 'forward'], [], '', false);

        $this->viewProphecy = $this->prophesize(TemplateView::class);
        $view = $this->viewProphecy->reveal();
        $this->subject->_set('view', $view);
    }

    /**
     * @test
     */
    public function isActionController(): void
    {
        self::assertInstanceOf(ActionController::class, $this->subject);
    }

    /**
     * @test
     * @doesNotPerformAssertions
     */
    public function newActionCanBeCalled(): void
    {
        $this->subject->newAction();
    }
}
