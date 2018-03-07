<?php

use Facebook\WebDriver\WebDriverBy;
use Codeception\Util\Locator;

class StepTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @param $args
     * @return Codeception\Step
     */
    protected function getStep($args)
    {
        return $this->getMockBuilder('\Codeception\Step')->setConstructorArgs($args)->setMethods(null)->getMock();
    }

    public function testGetArguments()
    {
        $by = WebDriverBy::cssSelector('.something');
        $step = $this->getStep([null, [$by]]);
        $this->assertEquals('"' . Locator::humanReadableString($by) . '"', $step->getArgumentsAsString());

        $step = $this->getStep([null, [['just', 'array']]]);
        $this->assertEquals('["just","array"]', $step->getArgumentsAsString());

        $step = $this->getStep([null, [function () {
        }]]);
        $this->assertEquals('"Closure"', $step->getArgumentsAsString());

        $step = $this->getStep([null, [[$this, 'testGetArguments']]]);
        $this->assertEquals('["StepTest","testGetArguments"]', $step->getArgumentsAsString());

        $step = $this->getStep([null, [['PDO', 'getAvailableDrivers']]]);
        $this->assertEquals('["PDO","getAvailableDrivers"]', $step->getArgumentsAsString());
    }

    public function testGetHtml()
    {
        $step = $this->getStep(['Do some testing', ['arg1', 'arg2']]);
        $this->assertSame('I do some testing <span style="color: #732E81">&quot;arg1&quot;,&quot;arg2&quot;</span>', $step->getHtml());

        $step = $this->getStep(['Do some testing', []]);
        $this->assertSame('I do some testing', $step->getHtml());
    }

    public function testLongArguments()
    {
        $step = $this->getStep(['have in database', [str_repeat('a', 2000)]]);
        $output = $step->toString(200);
        $this->assertLessThan(201, strlen($output), 'Output is too long: ' . $output);

        $step = $this->getStep(['have in database', [str_repeat('a', 100), str_repeat('b', 100)]]);
        $output = $step->toString(50);
        $this->assertEquals(50, strlen($output), 'Incorrect length of output: ' . $output);
        $this->assertEquals('have in database "aaaaaaaaaaa...","bbbbbbbbbbb..."', $output);

        $step = $this->getStep(['have in database', [1, str_repeat('b', 100)]]);
        $output = $step->toString(50);
        $this->assertEquals('have in database 1,"bbbbbbbbbbbbbbbbbbbbbbbbbb..."', $output);

        $step = $this->getStep(['have in database', [str_repeat('b', 100), 1]]);
        $output = $step->toString(50);
        $this->assertEquals('have in database "bbbbbbbbbbbbbbbbbbbbbbbbbb...",1', $output);
    }

    public function testArrayAsArgument()
    {
        $step = $this->getStep(['see array', [[1,2,3], 'two']]);
        $output = $step->toString(200);
        $this->assertEquals('see array [1,2,3],"two"', $output);
    }

    public function testSingleQuotedStringAsArgument()
    {
        $step = $this->getStep(['see array', [[1,2,3], "'two'"]]);
        $output = $step->toString(200);
        $this->assertEquals('see array [1,2,3],"\'two\'"', $output);
    }

    public function testSeeUppercaseText()
    {
        $step = $this->getStep(['see', ['UPPER CASE']]);
        $output = $step->toString(200);
        $this->assertEquals('see "UPPER CASE"', $output);
    }

    public function testMultiByteTextLengthIsMeasuredCorrectly()
    {
        $step = $this->getStep(['see', ['ŽŽŽŽŽŽŽŽŽŽ', 'AAAAAAAAAAA']]);
        $output = $step->toString(30);
        $this->assertEquals('see "ŽŽŽŽŽŽŽŽŽŽ","AAAAAAAAAAA"', $output);
    }

    public function testAmOnUrl()
    {
        $step = $this->getStep(['amOnUrl', ['http://www.example.org/test']]);
        $output = $step->toString(200);
        $this->assertEquals('am on url "http://www.example.org/test"', $output);
    }

    public function testNoArgs()
    {
        $step = $this->getStep(['acceptPopup', []]);
        $output = $step->toString(200);
        $this->assertEquals('accept popup ', $output);
        $output = $step->toString(-5);
        $this->assertEquals('accept popup ', $output);

    }

    public function testSeeMultiLineStringInSingleLine()
    {
        $step = $this->getStep(['see', ["aaaa\nbbbb\nc"]]);
        $output = $step->toString(200);
        $this->assertEquals('see "aaaa\nbbbb\nc"', $output);
    }

    public function testFormattedOutput()
    {
        $argument = Codeception\Util\Stub::makeEmpty('\Codeception\Step\Argument\FormattedOutput');
        $argument->method('getOutput')->willReturn('some formatted output');

        $step = $this->getStep(['argument', [$argument]]);
        $output = $step->toString(200);
        $this->assertEquals('argument "some formatted output"', $output);
    }
}
