<?php

declare(strict_types=1);

namespace Codeception\Util;

class TemplateTest extends \PHPUnit\Framework\TestCase
{
    public function testTemplateCanPassValues()
    {
        $template = new Template("hello, {{name}}");
        $template->place('name', 'davert');
        $this->assertSame('hello, davert', $template->produce());
    }

    public function testTemplateCanHaveOtherPlaceholder()
    {
        $template = new Template("hello, %name%", '%', '%');
        $template->place('name', 'davert');
        $this->assertSame('hello, davert', $template->produce());
    }

    public function testTemplateSupportsDotNotationForArrays()
    {
        $template = new Template("hello, {{user.data.name}}");
        $template->place('user', ['data' => ['name' => 'davert']]);
        $this->assertSame('hello, davert', $template->produce());
    }

    public function testShouldSkipUnmatchedPlaceholder()
    {
        $template = new Template("hello, {{name}}");
        $this->assertSame('hello, {{name}}', $template->produce());
    }
}
