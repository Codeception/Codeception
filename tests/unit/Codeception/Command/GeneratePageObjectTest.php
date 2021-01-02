<?php

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GeneratePageObjectTest extends BaseCommandRunner
{
    protected function _setUp()
    {
        $this->makeCommand(\Codeception\Command\GeneratePageObject::class);
        $this->config = [
            'actor' => 'HobbitGuy',
            'path' => 'tests/shire',
            'paths' => ['tests' => 'tests'],
            'settings' => ['bootstrap' => '_bootstrap.php']
        ];
    }

    public function testBasic()
    {
        unset($this->config['actor']);
        $this->execute(['suite' => 'Login'], false);
        $this->assertSame(\Codeception\Configuration::supportDir().'Page/Login.php', $this->filename);
        $this->assertStringContainsString('class Login', $this->content);
        $this->assertStringNotContainsString('public function __construct', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testNamespace()
    {
        unset($this->config['actor']);
        $this->config['namespace'] = 'MiddleEarth';
        $this->execute(['suite' => 'Login'], false);
        $this->assertSame(\Codeception\Configuration::supportDir().'Page/Login.php', $this->filename);
        $this->assertStringContainsString('namespace MiddleEarth\Page;', $this->content);
        $this->assertStringContainsString('class Login', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testCreateForSuite()
    {
        $this->execute(['suite' => 'shire', 'page' => 'Login']);
        $this->assertSame(\Codeception\Configuration::supportDir().'Page/Shire/Login.php', $this->filename);
        $this->assertStringContainsString('namespace Page\Shire;', $this->content);
        $this->assertStringContainsString('class Login', $this->content);
        $this->assertStringContainsString('protected $hobbitGuy;', $this->content);
        $this->assertStringContainsString('public function __construct(\HobbitGuy $I)', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testCreateForSuiteWithNamespace()
    {
        $this->config['namespace'] = 'MiddleEarth';
        $this->execute(['suite' => 'shire', 'page' => 'Login']);
        $this->assertSame(\Codeception\Configuration::supportDir().'Page/Shire/Login.php', $this->filename);
        $this->assertStringContainsString('namespace MiddleEarth\Page\Shire;', $this->content);
        $this->assertStringContainsString('class Login', $this->content);
        $this->assertStringContainsString('protected $hobbitGuy;', $this->content);
        $this->assertStringContainsString('public function __construct(\MiddleEarth\HobbitGuy $I)', $this->content);
        $this->assertIsValidPhp($this->content);
    }

    public function testCreateInSubpath()
    {
        $this->execute(['suite' => 'User/View']);
        $this->assertSame(\Codeception\Configuration::supportDir().'Page/User/View.php', $this->filename);
        $this->assertIsValidPhp($this->content);
    }
}
