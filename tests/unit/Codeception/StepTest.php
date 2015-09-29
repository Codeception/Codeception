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
}
