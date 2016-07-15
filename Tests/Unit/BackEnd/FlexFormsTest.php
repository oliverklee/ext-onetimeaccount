<?php
namespace OliverKlee\Onetimeaccount\Tests\Unit\BackEnd;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

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
