<?php

declare(strict_types=1);

namespace OliverKlee\OneTimeAccount\Tests\Functional;

use TYPO3\TestingFramework\Core\Functional\FunctionalTestCase;

/**
 * @coversNothing
 */
final class HelloWorldTest extends FunctionalTestCase
{
    /**
     * @var array<int, non-empty-string>
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/feuserextrafields',
        'typo3conf/ext/oelib',
        'typo3conf/ext/onetimeaccount',
    ];

    /**
     * @test
     */
    public function timeSpaceContinuumIsFine(): void
    {
        self::assertSame(4, 2 + 2);
    }
}
