<?php
require_once __DIR__.'/mocked_webelement.php';

class WebDriverConstraintNotTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Codeception\PHPUnit\Constraint\WebDriver
     */
    protected $constraint;

    public function setUp()
    {
        $this->constraint = new Codeception\PHPUnit\Constraint\WebDriverNot('warcraft','/user');
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
            $this->assertContains("There was 'selector' element on page <bold>/user</bold>", $fail->getMessage());
            $this->assertNotContains('+ <info><p> Bye world</info>',$fail->getMessage());
            $this->assertContains('+ <info><p> Bye <bold>warcraft</bold></info>',$fail->getMessage());
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
            $this->assertContains("There was css 'p.mocked' element on page <bold>/user</bold>", $fail->getMessage());
            $this->assertContains('+ <info><p> Bye <bold>warcraft</bold></info>',$fail->getMessage());
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
        } catch (PHPUnit_Framework_AssertionFailedError $fail) {
            $this->assertContains("There was 'selector' element on page <bold>/user</bold>", $fail->getMessage());
            $this->assertContains('+ <info><p> <bold>warcraft</bold> 0</info>',$fail->getMessage());
            $this->assertContains('+ <info><p> <bold>warcraft</bold> 14</info>',$fail->getMessage());
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
        } catch (PHPUnit_Framework_AssertionFailedError $fail) {
            $this->assertContains("There was 'selector' element", $fail->getMessage());
            $this->assertNotContains("There was 'selector' element on page", $fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }
}
