<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class RefactorAddNamespaceTest extends BaseCommandRunner
{
    protected function setUp()
    {
        $this->makeCommand('\Codeception\Command\RefactorAddNamespace');
    }

    public function testBasic()
    {
        $this->execute(array('namespace' => 'MiddleEarth', '--force' => true));
        $this->assertContains('adds namespaces to your Helper and Guy classes and Cepts', $this->output);
        
        $config = $this->log[0];
        $this->assertContains('Config file updated', $this->output);
        $this->assertContains('namespace: MiddleEarth', $config['content']);
        $this->assertEquals(\Codeception\Configuration::projectDir() . 'codeception.yml', (string)$config['filename']);
        
        $helper = $this->log[1];
        $this->assertContains('namespace MiddleEarth\\Codeception\\Module', $helper['content']);
        $this->assertRegExp('~'.\Codeception\Configuration::helpersDir().'.*?Helper~', (string)$helper['filename']);
        
        // log[2] is helper, log[3] ... are helpers too
        $cept = $this->log[8];
        $this->assertRegExp('~<?php use MiddleEarth\\\\.*Guy~', $cept['content']);
        $this->assertIsValidPhp($cept['content']);
    }

}
