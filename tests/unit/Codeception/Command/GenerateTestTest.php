<?php

declare(strict_types=1);

class GenerateTestTest extends BaseCommandRunner
{
    protected function _setUp()
    {
        $this->makeCommand(\Codeception\Command\GenerateTest::class);
        $this->config = [
            'actor' => 'HobbitGuy',
            'path' => 'tests/shire',
        ];
    }

    public function testBasic()
    {
        $this->execute(['suite' => 'shire', 'class' => 'HallUnderTheHill']);
        $this->assertSame('tests/shire/HallUnderTheHillTest.php', $this->filename);
        $this->assertStringContainsString('class HallUnderTheHillTest extends \Codeception\Test\Unit', $this->content);
        $this->assertStringContainsString('Test was created in tests/shire/HallUnderTheHillTest.php', $this->output);
        $this->assertStringContainsString('protected function _before()', $this->content);
    }

    public function testCreateWithSuffix()
    {
        $this->execute(['suite' => 'shire', 'class' => 'HallUnderTheHillTest']);
        $this->assertSame('tests/shire/HallUnderTheHillTest.php', $this->filename);
        $this->assertStringContainsString('Test was created in tests/shire/HallUnderTheHillTest.php', $this->output);
    }

    public function testCreateWithNamespace()
    {
        $this->execute(['suite' => 'shire', 'class' => 'MiddleEarth\HallUnderTheHillTest']);
        $this->assertSame('tests/shire/MiddleEarth/HallUnderTheHillTest.php', $this->filename);
        $this->assertStringContainsString('namespace Unit\MiddleEarth;', $this->content);
        $this->assertStringContainsString('class HallUnderTheHillTest extends \Codeception\Test\Unit', $this->content);
        $this->assertStringContainsString('Test was created in tests/shire/MiddleEarth/HallUnderTheHillTest.php', $this->output);
    }

    public function testCreateWithExtension()
    {
        $this->execute(['suite' => 'shire', 'class' => 'HallUnderTheHillTest.php']);
        $this->assertSame('tests/shire/HallUnderTheHillTest.php', $this->filename);
        $this->assertStringContainsString('class HallUnderTheHillTest extends \Codeception\Test\Unit', $this->content);
        $this->assertStringContainsString('protected HobbitGuy $tester;', $this->content);
        $this->assertStringContainsString('Test was created in tests/shire/HallUnderTheHillTest.php', $this->output);
    }

    public function testGenerateWithSupportNamespaced()
    {
        $this->config['namespace'] = 'MiddleEarth';
        $this->config['support_namespace'] = 'Gondor';
        $this->execute(['suite' => 'shire', 'class' => 'HallUnderTheHill']);
        $this->assertEquals($this->filename, 'tests/shire/HallUnderTheHillTest.php');
        $this->assertStringContainsString('namespace MiddleEarth\Unit;', $this->content);
        $this->assertStringContainsString('use MiddleEarth\\Gondor\\HobbitGuy;', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testValidPHP()
    {
        $this->execute(['suite' => 'shire', 'class' => 'HallUnderTheHill']);
        $this->assertIsValidPhp($this->content);
    }
}
