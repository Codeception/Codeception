<?php

use Facebook\WebDriver\WebDriverBy;
use Codeception\Util\Locator;

class StepTest extends \PHPUnit_Framework_TestCase
{
    public function testGetArguments()
    {
        $by = WebDriverBy::cssSelector('.something');
        $step = $this->getMockBuilder('\Codeception\Step')->setConstructorArgs([null, [$by]])->setMethods(null)->getMock();
        $this->assertEquals('"' . Locator::humanReadableString($by) . '"', $step->getArguments(true));
    }

    public function testGetHtml()
    {
        $step = $this->getMockBuilder('\Codeception\Step')->setConstructorArgs(['Do some testing', ['arg1', 'arg2']])->setMethods(null)->getMock();
        $this->assertSame('I do some testing <span style="color: #732E81">"arg1","arg2"</span>', $step->getHtml());

        $step = $this->getMockBuilder('\Codeception\Step')->setConstructorArgs(['Do some testing', []])->setMethods(null)->getMock();
        $this->assertSame('I do some testing', $step->getHtml());
    }
}
