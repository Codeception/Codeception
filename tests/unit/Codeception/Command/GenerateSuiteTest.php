<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateSuiteTest extends BaseCommandRunner
{
    public $config = ['actor_suffix' => 'Guy'];

    protected function _setUp()
    {
        $this->makeCommand('\Codeception\Command\GenerateSuite');
    }

    public function testBasic()
    {
        $this->execute(array('suite' => 'shire', 'actor' => 'Hobbit'), false);

        $configFile = $this->log[1];

        $this->assertEquals(\Codeception\Configuration::projectDir().'tests/shire.suite.yml', $configFile['filename']);
        $conf = \Symfony\Component\Yaml\Yaml::parse($configFile['content']);
        $this->assertEquals('Hobbit', $conf['actor']);
        $this->assertContains('\Helper\Shire', $conf['modules']['enabled']);
        $this->assertStringContainsString('Suite shire generated', $this->output);

        $actor = $this->log[2];
        $this->assertEquals(\Codeception\Configuration::supportDir().'Hobbit.php', $actor['filename']);
        $this->assertStringContainsString('class Hobbit extends \Codeception\Actor', $actor['content']);


        $helper = $this->log[0];
        $this->assertEquals(\Codeception\Configuration::supportDir().'Helper/Shire.php', $helper['filename']);
        $this->assertStringContainsString('namespace Helper;', $helper['content']);
        $this->assertStringContainsString('class Shire extends \Codeception\Module', $helper['content']);
    }

    public function testGuyWithSuffix()
    {
        $this->execute(array('suite' => 'shire', 'actor' => 'HobbitTester'), false);

        $configFile = $this->log[1];
        $conf = \Symfony\Component\Yaml\Yaml::parse($configFile['content']);
        $this->assertEquals('HobbitTester', $conf['actor']);
        $this->assertContains('\Helper\Shire', $conf['modules']['enabled']);

        $helper = $this->log[0];
        $this->assertEquals(\Codeception\Configuration::supportDir().'Helper/Shire.php', $helper['filename']);
        $this->assertStringContainsString('class Shire extends \Codeception\Module', $helper['content']);
    }
}
