<?php

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateGroupTest extends BaseCommandRunner
{
    /**
     * @var array
     */
    public $log = [];

    protected function _setUp(): void
    {
        $this->makeCommand(\Codeception\Command\GenerateGroup::class);
        $this->config = [
            'class_name' => 'HobbitGuy',
            'path' => 'tests/shire',
            'paths' => ['support' => 'tests/_support','tests' => 'tests'],
            'settings' => ['bootstrap' => '_bootstrap.php']
        ];
    }

    public function testBasic(): void
    {
        $this->execute(['group' => 'Core']);

        $generated = $this->log[0];
        $this->assertEquals(\Codeception\Configuration::supportDir().'Group/Core.php', $generated['filename']);
        $this->assertStringContainsString('namespace Group;', $generated['content']);
        $this->assertStringContainsString('class Core', $generated['content']);
        $this->assertStringContainsString('public function _before', $generated['content']);
        $this->assertStringContainsString('public function _after', $generated['content']);
        $this->assertStringContainsString('static $group = \'core\'', $generated['content']);
        $this->assertIsValidPhp($generated['content']);
    }

    public function testNamespace(): void
    {
        $this->config['namespace'] = 'Shire';
        $this->execute(['group' => 'Core']);

        $generated = $this->log[0];
        $this->assertEquals(\Codeception\Configuration::supportDir().'Group/Core.php', $generated['filename']);
        $this->assertStringContainsString('namespace Shire\Group;', $generated['content']);
        $this->assertIsValidPhp($generated['content']);
    }
}
