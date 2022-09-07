<?php

declare(strict_types=1);

namespace OliverKlee\Onetimeaccount\Tests\Unit\Service;

use OliverKlee\Onetimeaccount\Service\Autologin;
use TYPO3\CMS\Core\SingletonInterface;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @covers \OliverKlee\Onetimeaccount\Service\Autologin
 */
final class AutologinTest extends UnitTestCase
{
    /**
     * @var Autologin
     *
     * We can make this property private once we drop support for TYPO3 V9.
     */
    protected $subject;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subject = new Autologin();
    }

    protected function tearDown(): void
    {
        unset($GLOBALS['TSFE']);
        parent::tearDown();
    }

    /**
     * @test
     */
    public function isSingleton(): void
    {
        self::assertInstanceOf(SingletonInterface::class, $this->subject);
    }
}
