<?php
require_once __DIR__.'/mocked_webelement.php';

class WebDriverConstraintTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Codeception\PHPUnit\Constraint\WebDriver
     */
    protected $constraint;

    public function setUp()
    {
        $this->constraint = new Codeception\PHPUnit\Constraint\WebDriver('hello','/user');
    }

    public function testEvaluation()
    {
        $nodes = array(new TestedWebElement('Hello world'), new TestedWebElement('Bye world'));
        $this->constraint->evaluate($nodes);
    }

    public function testFailMessageResponse()
    {
        $nodes = array(new TestedWebElement('Bye warcraft'), new TestedWebElement('Bye world'));
        try {
            $this->constraint->evaluate($nodes, 'selector');
        } catch (PHPUnit_Framework_AssertionFailedError $fail) {
            $this->assertContains("Failed asserting that any element by 'selector' on page <bold>/user</bold>", $fail->getMessage());
            $this->assertContains('+ <info><p> Bye world</info>',$fail->getMessage());
            $this->assertContains('+ <info><p> Bye warcraft</info>',$fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }

    public function testFailMessageResponseWhenMoreNodes()
    {
        $nodes = array();
        for ($i = 0; $i < 15; $i++) {
            $nodes[] = new TestedWebElement("item $i");
        }
        try {
            $this->constraint->evaluate($nodes, 'selector');
        } catch (PHPUnit_Framework_AssertionFailedError $fail) {
            $this->assertContains("Failed asserting that any element by 'selector' on page <bold>/user</bold>", $fail->getMessage());
            $this->assertNotContains('+ <info><p> item 0</info>',$fail->getMessage());
            $this->assertNotContains('+ <info><p> item 14</info>',$fail->getMessage());
            $this->assertContains('<debug>[total 15 elements]</debug>', $fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }

    public function testFailMessageResponseWithoutUrl()
    {
        $this->constraint = new Codeception\PHPUnit\Constraint\WebDriver('hello');
        $nodes = array(new TestedWebElement('Bye warcraft'), new TestedWebElement('Bye world'));
        try {
            $this->constraint->evaluate($nodes, 'selector');
        } catch (PHPUnit_Framework_AssertionFailedError $fail) {
            $this->assertContains("Failed asserting that any element by 'selector'", $fail->getMessage());
            $this->assertNotContains("Failed asserting that any element by 'selector' on page", $fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }
}