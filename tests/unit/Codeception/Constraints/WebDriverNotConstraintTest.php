<?php

use Codeception\Util\Locator;

require_once __DIR__.'/mocked_webelement.php';

class WebDriverConstraintNotTest extends \Codeception\PHPUnit\TestCase
{

    /**
     * @var Codeception\PHPUnit\Constraint\WebDriverNot
     */
    protected $constraint;

    public function _setUp()
    {
        $this->constraint = new Codeception\PHPUnit\Constraint\WebDriverNot('warcraft', '/user');
    }

    public function testEvaluation()
    {
        $nodes = array(new TestedWebElement('Hello world'), new TestedWebElement('Bye world'));
        $this->constraint->evaluate($nodes);
    }

    public function testFailMessageResponseWithStringSelector()
    {
        $nodes = array(new TestedWebElement('Bye warcraft'), new TestedWebElement('Bye world'));
        try {
            $this->constraint->evaluate($nodes, 'selector');
        } catch (\PHPUnit\Framework\AssertionFailedError $fail) {
            $this->assertStringContainsString("There was 'selector' element on page /user", $fail->getMessage());
            $this->assertStringNotContainsString('+ <p> Bye world', $fail->getMessage());
            $this->assertStringContainsString('+ <p> Bye warcraft', $fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }

    public function testFailMessageResponseWithArraySelector()
    {
        $nodes = array(new TestedWebElement('Bye warcraft'));
        try {
            $this->constraint->evaluate($nodes, Locator::humanReadableString(['css' => 'p.mocked']));
        } catch (\PHPUnit\Framework\AssertionFailedError $fail) {
            $this->assertStringContainsString("There was css 'p.mocked' element on page /user", $fail->getMessage());
            $this->assertStringContainsString('+ <p> Bye warcraft', $fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }

    public function testFailMessageResponseWhenMoreNodes()
    {
        $nodes = array();
        for ($i = 0; $i < 15; $i++) {
            $nodes[] = new TestedWebElement("warcraft $i");
        }
        try {
            $this->constraint->evaluate($nodes, 'selector');
        } catch (\PHPUnit\Framework\AssertionFailedError $fail) {
            $this->assertStringContainsString("There was 'selector' element on page /user", $fail->getMessage());
            $this->assertStringContainsString('+ <p> warcraft 0', $fail->getMessage());
            $this->assertStringContainsString('+ <p> warcraft 14', $fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }

    public function testFailMessageResponseWithoutUrl()
    {
        $this->constraint = new Codeception\PHPUnit\Constraint\WebDriverNot('warcraft');
        $nodes = array(new TestedWebElement('Bye warcraft'), new TestedWebElement('Bye world'));
        try {
            $this->constraint->evaluate($nodes, 'selector');
        } catch (\PHPUnit\Framework\AssertionFailedError $fail) {
            $this->assertStringContainsString("There was 'selector' element", $fail->getMessage());
            $this->assertStringNotContainsString("There was 'selector' element on page", $fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }
}
