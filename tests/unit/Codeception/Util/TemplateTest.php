<?php

declare(strict_types=1);

namespace Codeception\Util;

class TemplateTest extends \PHPUnit\Framework\TestCase
{
    public function testTemplateCanPassValues(): void
    {
        $template = new Template("hello, {{name}}");
        $template->place('name', 'davert');
        $this->assertEquals('hello, davert', $template->produce());
    }

    public function testTemplateCanHaveOtherPlaceholder(): void
    {
        $template = new Template("hello, %name%", '%', '%');
        $template->place('name', 'davert');
        $this->assertEquals('hello, davert', $template->produce());
    }

    public function testTemplateSupportsDotNotationForArrays(): void
    {
        $template = new Template("hello, {{user.data.name}}");
        $template->place('user', ['data' => ['name' => 'davert']]);
        $this->assertEquals('hello, davert', $template->produce());
    }

    public function testShouldSkipUnmatchedPlaceholder(): void
    {
        $template = new Template("hello, {{name}}");
        $this->assertEquals('hello, {{name}}', $template->produce());
    }
}
