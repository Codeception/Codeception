<?php
class CrawlerNotConstraintTest extends PHPUnit_Framework_TestCase
{

    /**
     * @var Codeception\PHPUnit\Constraint\Crawler
     */
    protected $constraint;

    public function setUp()
    {
        $this->constraint = new Codeception\PHPUnit\Constraint\CrawlerNot('warcraft', '/user');
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
            $this->assertContains("There was 'selector' element on page <bold>/user</bold>", $fail->getMessage());
            $this->assertNotContains('+ <info><p>Bye world</p></info>', $fail->getMessage());
            $this->assertContains('+ <info><p>Bye <bold>warcraft</bold></p></info>', $fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }

    public function testFailMessageResponseWhenMoreNodes()
    {
        $html = '';
        for ($i = 0; $i < 15; $i++) {
            $html .= "<p>warcraft $i</p>";
        }
        $nodes = new Symfony\Component\DomCrawler\Crawler($html);
        try {
            $this->constraint->evaluate($nodes->filter('p'), 'selector');
        } catch (PHPUnit_Framework_AssertionFailedError $fail) {
            $this->assertContains("There was 'selector' element on page <bold>/user</bold>", $fail->getMessage());
            $this->assertContains('+ <info><p><bold>warcraft</bold> 0</p></info>', $fail->getMessage());
            $this->assertContains('+ <info><p><bold>warcraft</bold> 14</p></info>', $fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }

    public function testFailMessageResponseWithoutUrl()
    {
        $this->constraint = new Codeception\PHPUnit\Constraint\CrawlerNot('warcraft');
        $nodes = new Symfony\Component\DomCrawler\Crawler('<p>Bye world</p><p>Bye warcraft</p>');
        try {
            $this->constraint->evaluate($nodes->filter('p'), 'selector');
        } catch (PHPUnit_Framework_AssertionFailedError $fail) {
            $this->assertContains("There was 'selector' element", $fail->getMessage());
            $this->assertNotContains("There was 'selector' element on page <bold>/user</bold>", $fail->getMessage());
            return;
        }
        $this->fail("should have failed, but not");
    }
}
