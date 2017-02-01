<?php
require_once __DIR__.'/mocked_webelement.php';

class WebDriverConstraintTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Codeception\PHPUnit\Constraint\WebDriver
     */
    protected $constraint;

    public function setUp()
    {
        $this->constraint = new Codeception\PHPUnit\Constraint\WebDriver('hello', '/user');
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
        } catch (PHPUnit_Framework_AssertionFailedError $fail) {
            $this->assertContains(
                "Failed asserting that any element by 'selector' on page /user",
                $fail->getMessage()
            );
            $this->assertContains('+ <p> Bye world', $fail->getMessage());
            $this->assertContains('+ <p> Bye warcraft', $fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }

    public function testFailMessageResponseWithArraySelector()
    {
        $nodes = array(new TestedWebElement('Bye warcraft'));
        try {
            $this->constraint->evaluate($nodes, ['css' => 'p.mocked']);
        } catch (PHPUnit_Framework_AssertionFailedError $fail) {
            $this->assertContains(
                "Failed asserting that any element by css 'p.mocked' on page /user",
                $fail->getMessage()
            );
            $this->assertContains('+ <p> Bye warcraft', $fail->getMessage());
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
            $this->assertContains(
                "Failed asserting that any element by 'selector' on page /user",
                $fail->getMessage()
            );
            $this->assertNotContains('+ <p> item 0', $fail->getMessage());
            $this->assertNotContains('+ <p> item 14', $fail->getMessage());
            $this->assertContains('[total 15 elements]', $fail->getMessage());
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
