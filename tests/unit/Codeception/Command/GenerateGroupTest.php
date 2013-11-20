<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateGroupTest extends BaseCommandRunner {

    protected function setUp()
    {
        $this->makeCommand('\Codeception\Command\GenerateGroup');
        $this->config = array(
            'class_name' => 'HobbitGuy',
            'path' => 'tests/shire',
            'paths' => array('tests' => 'tests'),
            'settings' => array('bootstrap' => '_bootstrap.php')
        );
    }

    public function testBasic()
    {
        $this->execute(array('group' => 'Core'));

        $generated = $this->log[1];
        $this->assertEquals('tests/_groups/CoreGroup.php', $generated['filename']);
        $this->assertContains('class CoreGroup', $generated['content']);
        $this->assertContains('public function _before', $generated['content']);
        $this->assertContains('public function _after', $generated['content']);
        $this->assertContains('static $group = \'Core\'', $generated['content']);
        $this->assertIsValidPhp($generated['content']);
    }

    public function testNamespace()
    {
        $this->config['namespace'] = 'Shire';
        $this->execute(array('group' => 'Core'));

        $generated = $this->log[1];
        $this->assertEquals('tests/_groups/CoreGroup.php', $generated['filename']);
        $this->assertContains('namespace Shire;', $generated['content']);
        $this->assertIsValidPhp($generated['content']);
    }

}