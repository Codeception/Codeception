<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateGroupTest extends BaseCommandRunner
{

    protected function _setUp()
    {
        $this->makeCommand('\Codeception\Command\GenerateGroup');
        $this->config = array(
            'class_name' => 'HobbitGuy',
            'path' => 'tests/shire',
            'paths' => array('support' => 'tests/_support','tests' => 'tests'),
            'settings' => array('bootstrap' => '_bootstrap.php')
        );
    }

    public function testBasic()
    {
        $this->execute(array('group' => 'Core'));

        $generated = $this->log[0];
        $this->assertEquals(\Codeception\Configuration::supportDir().'Group/Core.php', $generated['filename']);
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
        $this->execute(array('group' => 'Core'));

        $generated = $this->log[0];
        $this->assertEquals(\Codeception\Configuration::supportDir().'Group/Core.php', $generated['filename']);
        $this->assertStringContainsString('namespace Shire\Group;', $generated['content']);
        $this->assertIsValidPhp($generated['content']);
    }
}
