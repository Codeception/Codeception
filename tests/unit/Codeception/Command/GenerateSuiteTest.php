<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateSuiteTest extends BaseCommandRunner
{
    public $config = ['actor' => 'Guy'];

    protected function setUp()
    {
        $this->makeCommand('\Codeception\Command\GenerateSuite');
    }

    public function testBasic()
    {
        $this->execute(array('suite' => 'shire', 'actor' => 'Hobbit'), false);

        $configFile = $this->log[1];

        $this->assertEquals(\Codeception\Configuration::projectDir().'tests/shire.suite.yml', $configFile['filename']);
        $conf = \Symfony\Component\Yaml\Yaml::parse($configFile['content']);
        $this->assertEquals('HobbitGuy', $conf['class_name']);
        $this->assertContains('\Helper\Hobbit', $conf['modules']['enabled']);
        $this->assertContains('Suite shire generated', $this->output);

        $actor = $this->log[2];
        $this->assertEquals(\Codeception\Configuration::supportDir().'HobbitGuy.php', $actor['filename']);
        $this->assertContains('class HobbitGuy extends \Codeception\Actor', $actor['content']);


        $helper = $this->log[0];
        $this->assertEquals(\Codeception\Configuration::supportDir().'Helper/Hobbit.php', $helper['filename']);
        $this->assertContains('namespace Helper;', $helper['content']);
        $this->assertContains('class Hobbit extends \Codeception\Module', $helper['content']);
    }

    public function testGuyWithSuffix()
    {
        $this->execute(array('suite' => 'shire', 'actor' => 'HobbitGuy'), false);

        $configFile = $this->log[1];
        $conf = \Symfony\Component\Yaml\Yaml::parse($configFile['content']);
        $this->assertEquals('HobbitGuy', $conf['class_name']);
        $this->assertContains('\Helper\Hobbit', $conf['modules']['enabled']);

        $helper = $this->log[0];
        $this->assertEquals(\Codeception\Configuration::supportDir().'Helper/Hobbit.php', $helper['filename']);
        $this->assertContains('class Hobbit extends \Codeception\Module', $helper['content']);
    }
}
