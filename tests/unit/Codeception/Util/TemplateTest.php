<?php
namespace Codeception\Util;

class TemplateTest extends \PHPUnit_Framework_TestCase
{
    public function testTemplateCanPassValues()
    {
        $template = new Template("hello, {{name}}");
        $template->place('name', 'davert');
        $this->assertEquals('hello, davert', $template->produce());
    }

    public function testTemplateCanHaveOtherPlaceholder()
    {
        $template = new Template("hello, %name%",'%','%');
        $template->place('name', 'davert');
        $this->assertEquals('hello, davert', $template->produce());
    }

    public function testShouldSkipUnmatchedPlaceholder()
    {
         $template = new Template("hello, {{name}}");
         $this->assertEquals('hello, {{name}}', $template->produce());
    }
}