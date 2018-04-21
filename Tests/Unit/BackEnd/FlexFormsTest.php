<?php
namespace OliverKlee\Onetimeaccount\Tests\Unit\BackEnd;

use OliverKlee\Onetimeaccount\BackEnd\FlexForms;

/**
 * Test case.
 *
 * @author Oliver Klee <typo3-coding@oliverklee.de>
 */
class FlexFormsTest extends \PHPUnit_Framework_TestCase
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
