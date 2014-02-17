<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateSuiteTest extends BaseCommandRunner {

    protected function setUp()
    {
        $this->makeCommand('\Codeception\Command\GenerateSuite');
    }

    public function testBasic()
    {
        $this->execute(array('suite' => 'shire', 'actor' => 'Hobbit'));
        $this->assertEquals(\Codeception\Configuration::projectDir().'tests/shire.suite.yml',$this->filename);
        $conf = \Symfony\Component\Yaml\Yaml::parse($this->content);
        $this->assertEquals('HobbitGuy',$conf['class_name']);
        $this->assertContains('HobbitHelper',$conf['modules']['enabled']);
        $this->assertContains('Suite shire generated', $this->output);

        $helper = $this->log[1];
        $this->assertEquals(\Codeception\Configuration::helpersDir().'HobbitHelper.php',$helper['filename']);
        $this->assertContains('class HobbitHelper extends \Codeception\Module', $helper['content']);

        $bootstrap = $this->log[0];
        $this->assertEquals(\Codeception\Configuration::projectDir().'tests/shire/_bootstrap.php',$bootstrap['filename']);
    }

    public function testGuyWithSuffix()
    {
        $this->execute(array('suite' => 'shire', 'actor' => 'HobbitGuy'));
        $conf = \Symfony\Component\Yaml\Yaml::parse($this->content);
        $this->assertEquals('HobbitGuy',$conf['class_name']);
        $this->assertContains('HobbitHelper',$conf['modules']['enabled']);

        $helper = $this->log[1];
        $this->assertEquals(\Codeception\Configuration::helpersDir().'HobbitHelper.php',$helper['filename']);
        $this->assertContains('class HobbitHelper extends \Codeception\Module', $helper['content']);
    }


}
