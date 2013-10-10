<?php
class CrawlerConstraintTest extends PHPUnit_Framework_TestCase {

    /**
     * @var Codeception\PHPUnit\Constraint\Crawler
     */
    protected $constraint;

    public function setUp()
    {
        $this->constraint = new Codeception\PHPUnit\Constraint\Crawler('hello','/user');
    }

    public function testEvaluation()
    {
        $nodes = new Symfony\Component\DomCrawler\Crawler("<p>Bye world</p><p>Hello world</p>");
        $this->constraint->evaluate($nodes);
    }

    public function testFailMessageResponse()
    {
        $nodes = new Symfony\Component\DomCrawler\Crawler('<p>Bye world</p><p>Bye warcraft</p>');
        try {
            $this->constraint->evaluate($nodes->filter('p'), 'selector');
        } catch (PHPUnit_Framework_AssertionFailedError $fail) {
            $this->assertContains("Failed asserting that any element by 'selector' on page <bold>/user</bold>", $fail->getMessage());
            $this->assertContains('+ <info><p>Bye world</p></info>',$fail->getMessage());
            $this->assertContains('+ <info><p>Bye warcraft</p></info>',$fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }

    public function testFailMessageResponseWhenMoreNodes()
    {
        $html = '';
        for ($i = 0; $i < 15; $i++) {
            $html .= "<p>item $i</p>";
        }
        $nodes = new Symfony\Component\DomCrawler\Crawler($html);
        try {
            $this->constraint->evaluate($nodes->filter('p'), 'selector');
        } catch (PHPUnit_Framework_AssertionFailedError $fail) {
            $this->assertContains("Failed asserting that any element by 'selector' on page <bold>/user</bold>", $fail->getMessage());
            $this->assertNotContains('+ <info><p>item 0</p></info>',$fail->getMessage());
            $this->assertNotContains('+ <info><p>item 14</p></info>',$fail->getMessage());
            $this->assertContains('<debug>[total 15 elements]</debug>', $fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }

    public function testFailMessageResponseWithoutUrl()
    {
        $this->constraint = new Codeception\PHPUnit\Constraint\Crawler('hello');
        $nodes = new Symfony\Component\DomCrawler\Crawler('<p>Bye world</p><p>Bye warcraft</p>');
        try {
            $this->constraint->evaluate($nodes->filter('p'), 'selector');
        } catch (PHPUnit_Framework_AssertionFailedError $fail) {
            $this->assertContains("Failed asserting that any element by 'selector'", $fail->getMessage());
            $this->assertNotContains("Failed asserting that any element by 'selector' on page", $fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }



}
