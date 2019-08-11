<?php

namespace OliverKlee\OneTimeAccount\Tests\LegacyUnit\BackEnd;

use OliverKlee\OneTimeAccount\BackEnd\FlexForms;
use OliverKlee\PhpUnit\TestCase;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FlexFormsTest extends TestCase
{
    /**
     * @var bool
     */
    protected $backupGlobals = false;

    /**
     * @var FlexForms
     */
    private $subject = null;

    protected function setUp()
    {
        $this->subject = new FlexForms();
    }

    /**
     * @test
     */
    public function classCanBeInstantiated()
    {
        self::assertInstanceOf(FlexForms::class, $this->subject);
    }
}
