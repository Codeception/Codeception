<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateStepObjectTest extends BaseCommandRunner
{

    protected function _setUp()
    {
        $this->makeCommand('\Codeception\Command\GenerateStepObject');
        $this->config = array(
            'actor' => 'HobbitGuy',
            'path' => 'tests/shire',
        );
    }

    public function testBasic()
    {
        $this->execute(array('suite' => 'shire', 'step' => 'Login', '--silent' => true));

        $generated = $this->log[0];
        $this->assertEquals(\Codeception\Configuration::supportDir().'Step/Shire/Login.php', $generated['filename']);
        $this->assertStringContainsString('class Login extends \HobbitGuy', $generated['content']);
        $this->assertStringContainsString('namespace Step\\Shire;', $generated['content']);
        $this->assertIsValidPhp($generated['content']);

        $this->assertIsValidPhp($this->content);
    }

    public function testNamespace()
    {
        $this->config['namespace'] = 'MiddleEarth';
        $this->execute(array('suite' => 'shire', 'step' => 'Login', '--silent' => true));
        $generated = $this->log[0];
        $this->assertEquals(\Codeception\Configuration::supportDir().'Step/Shire/Login.php', $generated['filename']);
        $this->assertStringContainsString('namespace MiddleEarth\Step\Shire;', $generated['content']);
        $this->assertStringContainsString('class Login extends \MiddleEarth\HobbitGuy', $generated['content']);
        $this->assertIsValidPhp($generated['content']);

        $this->assertIsValidPhp($this->content);
    }

    public function testCreateInSubpath()
    {
        $this->execute(array('suite' => 'shire', 'step' => 'User/Login', '--silent' => true));
        $generated = $this->log[0];
        $this->assertEquals(
            \Codeception\Configuration::supportDir().'Step/Shire/User/Login.php',
            $generated['filename']
        );
        $this->assertIsValidPhp($this->content);
    }
}
