<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Functional\Controller;

use OliverKlee\Onetimeaccount\Controller\UserWithAutologinController;

/**
 * Note: This test should be a unit test, but `GeneralUtility::flushInternalRuntimeCaches` currently cannot flush
 * the class name cache yet.
 *
 * @extends AbstractUserControllerTest<UserWithAutologinController>
 *
 * @covers \OliverKlee\Onetimeaccount\Controller\UserWithAutologinController
 * @covers \OliverKlee\Onetimeaccount\Controller\AbstractUserController
 */
final class UserWithAutologinControllerTest extends AbstractUserControllerTest
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setDummyRequestData();

        // We need to create an accessible mock in order to be able to set the protected `view`.
        // We can drop the additional arguments to skip the original constructor once we drop support for TYPO3 V9.
        $this->subject = $this->getAccessibleMock(
            UserWithAutologinController::class,
            ['redirect', 'forward', 'redirectToUri'],
            [],
            '',
            false
        );

        $this->setUpAndInjectSharedDependencies();
    }
}
