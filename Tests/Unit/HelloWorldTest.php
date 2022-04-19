<?php

declare(strict_types=1);

namespace OliverKlee\OneTimeAccount\Tests\Unit;

use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversNothing
 */
final class HelloWorldTest extends UnitTestCase
{
    /**
     * @test
     */
    public function timeSpaceContinuumIsFine(): void
    {
        self::assertSame(4, 2 + 2);
    }
}
