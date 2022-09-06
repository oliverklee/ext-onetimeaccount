<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Controller;

use OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController;

/**
 * @extends AbstractUserControllerTest<UserWithoutAutologinController>
 *
 * @covers \OliverKlee\Onetimeaccount\Controller\UserWithoutAutologinController
 * @covers \OliverKlee\Onetimeaccount\Controller\AbstractUserController
 */
final class UserWithoutAutologinControllerTest extends AbstractUserControllerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setDummyRequestData();

        // We need to create an accessible mock in order to be able to set the protected `view`.
        // We can drop the additional arguments to skip the original constructor once we drop support for TYPO3 V9.
        $this->subject = $this->getAccessibleMock(
            UserWithoutAutologinController::class,
            ['redirect', 'forward', 'redirectToUri'],
            [],
            '',
            false
        );

        $this->setUpAndInjectSharedDependencies();
    }
}
