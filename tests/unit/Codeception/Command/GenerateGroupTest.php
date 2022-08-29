<?php

declare(strict_types=1);

class GenerateGroupTest extends BaseCommandRunner
{
    /**
     * @var array
     */
    public array $log = [];

    protected function _setUp()
    {
        $this->makeCommand(\Codeception\Command\GenerateGroup::class);
        $this->config = [
            'actor' => 'HobbitGuy',
            'path' => 'tests/shire',
            'paths' => ['support' => 'tests/_support','tests' => 'tests'],
            'settings' => ['bootstrap' => '_bootstrap.php']
        ];
    }

    public function testBasic()
    {
        $this->execute(['group' => 'Core']);

        $generated = $this->log[0];
        $this->assertSame(\Codeception\Configuration::supportDir() . 'Group/Core.php', $generated['filename']);
        $this->assertStringContainsString('namespace Group;', $generated['content']);
        $this->assertStringContainsString('class Core', $generated['content']);
        $this->assertStringContainsString('public function _before', $generated['content']);
        $this->assertStringContainsString('public function _after', $generated['content']);
        $this->assertStringContainsString('static $group = \'core\'', $generated['content']);
        $this->assertIsValidPhp($generated['content']);
    }

    public function testNamespace()
    {
        $this->config['namespace'] = 'Shire';
        $this->execute(['group' => 'Core']);

        $generated = $this->log[0];
        $this->assertSame(\Codeception\Configuration::supportDir() . 'Group/Core.php', $generated['filename']);
        $this->assertStringContainsString('namespace Shire\Group;', $generated['content']);
        $this->assertIsValidPhp($generated['content']);
    }
}
