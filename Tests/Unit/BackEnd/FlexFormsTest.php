<?php

namespace OliverKlee\OneTimeAccount\Tests\Unit\BackEnd;

use Nimut\TestingFramework\TestCase\UnitTestCase;
use OliverKlee\OneTimeAccount\BackEnd\FlexForms;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FlexFormsTest extends UnitTestCase
{
    /**
     * @test
     */
    public function classExists()
    {
        self::assertTrue(\class_exists(FlexForms::class));
    }
}
