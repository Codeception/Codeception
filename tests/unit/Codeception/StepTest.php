<?php

use Facebook\WebDriver\WebDriverBy;
use Codeception\Util\Locator;

class StepTest extends \PHPUnit_Framework_TestCase
{
    protected function getStep($args)
    {
        return $this->getMockBuilder('\Codeception\Step')->setConstructorArgs($args)->setMethods(null)->getMock();
    }

    public function testGetArguments()
    {
        $by = WebDriverBy::cssSelector('.something');
        $step = $this->getStep([null, [$by]]);
        $this->assertEquals('"' . Locator::humanReadableString($by) . '"', $step->getArguments(true));

        $step = $this->getStep([null, [['just', 'array']]]);
        $this->assertEquals('"just","array"', $step->getArguments(true));

        $step = $this->getStep([null, [function(){}]]);
        $this->assertEquals('"lambda function"', $step->getArguments(true));

        $step = $this->getStep([null, [[$this, 'testGetArguments']]]);
        $this->assertEquals('"callable function"', $step->getArguments(true));
    }

    public function testGetHtml()
    {
        $step = $this->getStep(['Do some testing', ['arg1', 'arg2']]);
        $this->assertSame('I do some testing <span style="color: #732E81">"arg1","arg2"</span>', $step->getHtml());

        $step = $this->getStep(['Do some testing', []]);
        $this->assertSame('I do some testing', $step->getHtml());
    }
}
