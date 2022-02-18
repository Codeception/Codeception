<?php

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateCestTest extends BaseCommandRunner
{
    protected function _setUp()
    {
        $this->makeCommand(\Codeception\Command\GenerateCest::class);
        $this->config = [
            'actor' => 'HobbitGuy',
            'path' => 'tests/shire',
        ];
    }

    /**
     * @group command
     */
    public function testBasic()
    {
        $this->execute(['suite' => 'shire', 'class' => 'HallUnderTheHill']);
        $this->assertSame('tests/shire/HallUnderTheHillCest.php', $this->filename);

        $this->assertStringContainsString('class HallUnderTheHillCest', $this->content);
        $this->assertStringContainsString('public function _before(HobbitGuy $I)', $this->content);
        $this->assertStringContainsString('public function tryToTest(HobbitGuy $I)', $this->content);
        $this->assertStringContainsString('Test was created in tests/shire/HallUnderTheHillCest.php', $this->output);
    }

    /**
     * @group command
     */
    public function testNamespaced()
    {
        $this->config['namespace'] = 'Shire';
        $this->execute(['suite' => 'shire', 'class' => 'HallUnderTheHill']);
        $this->assertStringContainsString('namespace Shire\Unit;', $this->content);
        $this->assertStringContainsString('use \Shire\HobbitGuy;', $this->content);
        $this->assertStringContainsString('class HallUnderTheHillCest', $this->content);
    }

    /**
     * @group command
     */
    public function testGenerateWithFullName()
    {
        $this->execute(['suite' => 'shire', 'class' => 'HomeCanInclude12DwarfsCest.php']);
        $this->assertSame('tests/shire/HomeCanInclude12DwarfsCest.php', $this->filename);
    }

    /**
     * @group command
     */
    public function testGenerateWithSuffix()
    {
        $this->execute(['suite' => 'shire', 'class' => 'HomeCanInclude12DwarfsCest']);
        $this->assertSame($this->filename, 'tests/shire/HomeCanInclude12DwarfsCest.php');
        $this->assertIsValidPhp($this->content);
    }

    public function testGenerateWithActorNamespaced()
    {
        $this->config['namespace'] = 'MiddleEarth';
        $this->execute(['suite' => 'shire', 'class' => 'HallUnderTheHillCest']);
        $this->assertSame($this->filename, 'tests/shire/HallUnderTheHillCest.php');
        $this->assertStringContainsString('namespace MiddleEarth\Unit;', $this->content);
        $this->assertStringContainsString('use \MiddleEarth\\HobbitGuy;', $this->content);
        $this->assertStringContainsString('public function tryToTest(HobbitGuy $I)', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testCreateWithNamespace()
    {
        $this->execute(['suite' => 'shire', 'class' => 'MiddleEarth\HallUnderTheHillCest']);
        $this->assertSame('tests/shire/MiddleEarth/HallUnderTheHillCest.php', $this->filename);
        $this->assertStringContainsString('namespace Unit\MiddleEarth;', $this->content);
        $this->assertStringContainsString('class HallUnderTheHillCest', $this->content);
        $this->assertStringContainsString('Test was created in tests/shire/MiddleEarth/HallUnderTheHillCest.php', $this->output);
    }

    public function testGenerateWithSupportNamespaced()
    {
        $this->config['namespace'] = 'MiddleEarth';
        $this->config['support_namespace'] = 'Gondor';
        $this->execute(['suite' => 'shire', 'class' => 'HallUnderTheHillCest']);
        $this->assertSame($this->filename, 'tests/shire/HallUnderTheHillCest.php');
        $this->assertStringContainsString('namespace MiddleEarth\Unit;', $this->content);
        $this->assertStringContainsString('use \MiddleEarth\\Gondor\\HobbitGuy;', $this->content);
        $this->assertStringContainsString('public function tryToTest(HobbitGuy $I)', $this->content);
        $this->assertIsValidPhp($this->content);
    }
}
