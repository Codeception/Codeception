<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateStepObjectTest extends BaseCommandRunner {

    protected function setUp()
    {
        $this->makeCommand('\Codeception\Command\GenerateStepObject');
        $this->config = array(
            'class_name' => 'HobbitGuy',
            'path' => 'tests/shire',
        );
    }

    public function testBasic()
    {
        $this->execute(array('suite' => 'shire', 'step' => 'Login', '--silent' => true));

        $generated = $this->log[0];
        $this->assertEquals('tests/shire/_steps/LoginSteps.php', $generated['filename']);
        $this->assertContains('class LoginSteps extends \HobbitGuy', $generated['content']);
        $this->assertContains('namespace HobbitGuy;', $generated['content']);
        $this->assertIsValidPhp($generated['content']);

        $bootstrap = $this->content;
        $this->assertContains("\\Codeception\\Util\\Autoload::registerSuffix('Steps', __DIR__.DIRECTORY_SEPARATOR.'_steps');", $bootstrap);
        $this->assertIsValidPhp($bootstrap);

    }

    public function testNamespace()
    {
        $this->config['namespace'] = 'MiddleEarth';
        $this->execute(array('suite' => 'shire', 'step' => 'Login', '--silent' => true));
        $generated = $this->log[0];
        $this->assertEquals('tests/shire/_steps/LoginSteps.php', $generated['filename']);
        $this->assertContains('namespace MiddleEarth\HobbitGuy;', $generated['content']);
        $this->assertContains('class LoginSteps extends \MiddleEarth\HobbitGuy', $generated['content']);
        $this->assertIsValidPhp($generated['content']);
        
        $bootstrap = $this->content;
        $this->assertContains("\\Codeception\\Util\\Autoload::registerSuffix('Steps', __DIR__.DIRECTORY_SEPARATOR.'_steps');", $bootstrap);
        $this->assertIsValidPhp($bootstrap);
    }

     public function testCreateInSubpath()
     {
         $this->execute(array('suite' => 'shire', 'step' => 'user/Login', '--silent' => true));
         $generated = $this->log[0];
         $this->assertEquals('tests/shire/_steps/LoginSteps.php', $generated['filename']);
         $this->assertIsValidPhp($this->content);
     }


}