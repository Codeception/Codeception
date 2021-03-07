<?php

declare(strict_types=1);

use Codeception\Step;
use Codeception\Step\Argument\FormattedOutput;
use Codeception\Stub;
use Facebook\WebDriver\WebDriverBy;
use PHPUnit\Framework\TestCase;

class StepTest extends TestCase
{
    protected function getStep(array $args): Step
    {
        return $this->getMockBuilder('\Codeception\Step')
            ->setConstructorArgs($args)
            ->onlyMethods([])
            ->getMock();
    }

    public function testGetArguments()
    {
        //facebook/php-webdriver is no longer a dependency of core so this behaviour can't be tested anymore
        //$by = WebDriverBy::cssSelector('.something');
        //$step = $this->getStep(['', [$by]]);
        //$this->assertSame('"' . Locator::humanReadableString($by) . '"', $step->getArgumentsAsString());

        $step = $this->getStep(['', [['just', 'array']]]);
        $this->assertSame('["just","array"]', $step->getArgumentsAsString());

        $step = $this->getStep(['', [function () {
        }]]);
        $this->assertSame('"Closure"', $step->getArgumentsAsString());

        $step = $this->getStep(['', [[$this, 'testGetArguments']]]);
        $this->assertSame('["StepTest","testGetArguments"]', $step->getArgumentsAsString());

        $step = $this->getStep(['', [[PDO::class, 'getAvailableDrivers']]]);
        $this->assertSame('["PDO","getAvailableDrivers"]', $step->getArgumentsAsString());

        $step = $this->getStep(['', [[Stub::make($this, []), 'testGetArguments']]]);
        $this->assertSame('["StepTest","testGetArguments"]', $step->getArgumentsAsString());

        $mock = $this->createMock(get_class($this));
        $step = $this->getStep(['', [[$mock, 'testGetArguments']]]);
        $className = get_class($mock);
        $this->assertSame('["' . $className . '","testGetArguments"]', $step->getArgumentsAsString());
    }

    public function testGetHtml()
    {
        $step = $this->getStep(['Do some testing', ['arg1', 'arg2']]);
        $this->assertSame('I do some testing <span style="color: #732E81">&quot;arg1&quot;,&quot;arg2&quot;</span>', $step->getHtml());

        $step = $this->getStep(['Do some testing', []]);
        $this->assertSame('I do some testing', $step->getHtml());

        $argument = str_repeat("A string with a length exceeding Step::DEFAULT_MAX_LENGTH.", Step::DEFAULT_MAX_LENGTH);
        $step = $this->getStep(['Do some testing', [$argument]]);
        $this->assertSame('I do some testing <span style="color: #732E81">&quot;' . $argument . '&quot;</span>', $step->getHtml());
    }

    public function testLongArguments()
    {
        $step = $this->getStep(['have in database', [str_repeat('a', 2000)]]);
        $output = $step->toString(200);
        $this->assertLessThan(201, strlen($output), 'Output is too long: ' . $output);

        $step = $this->getStep(['have in database', [str_repeat('a', 100), str_repeat('b', 100)]]);
        $output = $step->toString(50);
        $this->assertSame(50, strlen($output), 'Incorrect length of output: ' . $output);
        $this->assertSame('have in database "aaaaaaaaaaa...","bbbbbbbbbbb..."', $output);

        $step = $this->getStep(['have in database', [1, str_repeat('b', 100)]]);
        $output = $step->toString(50);
        $this->assertSame('have in database 1,"bbbbbbbbbbbbbbbbbbbbbbbbbb..."', $output);

        $step = $this->getStep(['have in database', [str_repeat('b', 100), 1]]);
        $output = $step->toString(50);
        $this->assertSame('have in database "bbbbbbbbbbbbbbbbbbbbbbbbbb...",1', $output);
    }

    public function testArrayAsArgument()
    {
        $step = $this->getStep(['see array', [[1,2,3], 'two']]);
        $output = $step->toString(200);
        $this->assertSame('see array [1,2,3],"two"', $output);
    }

    public function testSingleQuotedStringAsArgument()
    {
        $step = $this->getStep(['see array', [[1,2,3], "'two'"]]);
        $output = $step->toString(200);
        $this->assertSame('see array [1,2,3],"\'two\'"', $output);
    }

    public function testSeeUppercaseText()
    {
        $step = $this->getStep(['see', ['UPPER CASE']]);
        $output = $step->toString(200);
        $this->assertSame('see "UPPER CASE"', $output);
    }

    public function testMultiByteTextLengthIsMeasuredCorrectly()
    {
        $step = $this->getStep(['see', ['ŽŽŽŽŽŽŽŽŽŽ', 'AAAAAAAAAAA']]);
        $output = $step->toString(30);
        $this->assertSame('see "ŽŽŽŽŽŽŽŽŽŽ","AAAAAAAAAAA"', $output);
    }

    public function testAmOnUrl()
    {
        $step = $this->getStep(['amOnUrl', ['http://www.example.org/test']]);
        $output = $step->toString(200);
        $this->assertSame('am on url "http://www.example.org/test"', $output);
    }

    public function testNoArgs()
    {
        $step = $this->getStep(['acceptPopup', []]);
        $output = $step->toString(200);
        $this->assertSame('accept popup ', $output);
        $output = $step->toString(-5);
        $this->assertSame('accept popup ', $output);

    }

    public function testSeeMultiLineStringInSingleLine()
    {
        $step = $this->getStep(['see', ["aaaa\nbbbb\nc"]]);
        $output = $step->toString(200);
        $this->assertSame('see "aaaa\nbbbb\nc"', $output);
    }

    public function testFormattedOutput()
    {
        $argument = Stub::makeEmpty(FormattedOutput::class);
        $argument->method('getOutput')->willReturn('some formatted output');

        $step = $this->getStep(['argument', [$argument]]);
        $output = $step->toString(200);
        $this->assertSame('argument "some formatted output"', $output);
    }
}
