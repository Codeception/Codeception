<?php

declare(strict_types=1);

class GenerateStepObjectTest extends BaseCommandRunner
{
    /**
     * @var array
     */
    public array $log = [];

    protected function _setUp()
    {
        $this->makeCommand(\Codeception\Command\GenerateStepObject::class);
        $this->config = [
            'actor' => 'HobbitGuy',
            'path' => 'tests/shire',
        ];
    }

    public function testBasic()
    {
        $this->execute(['suite' => 'shire', 'step' => 'Login', '--silent' => true]);

        $generated = $this->log[0];
        $this->assertSame(\Codeception\Configuration::supportDir() . 'Step/Shire/Login.php', $generated['filename']);
        $this->assertStringContainsString('class Login extends \HobbitGuy', $generated['content']);
        $this->assertStringContainsString('namespace Step\\Shire;', $generated['content']);
        $this->assertIsValidPhp($generated['content']);

        $this->assertIsValidPhp($this->content);
    }

    public function testNamespace()
    {
        $this->config['namespace'] = 'MiddleEarth';
        $this->execute(['suite' => 'shire', 'step' => 'Login', '--silent' => true]);
        $generated = $this->log[0];
        $this->assertSame(\Codeception\Configuration::supportDir() . 'Step/Shire/Login.php', $generated['filename']);
        $this->assertStringContainsString('namespace MiddleEarth\Step\Shire;', $generated['content']);
        $this->assertStringContainsString('class Login extends \MiddleEarth\HobbitGuy', $generated['content']);
        $this->assertIsValidPhp($generated['content']);

        $this->assertIsValidPhp($this->content);
    }

    public function testCreateInSubpath()
    {
        $this->execute(['suite' => 'shire', 'step' => 'User/Login', '--silent' => true]);
        $generated = $this->log[0];
        $this->assertSame(
            \Codeception\Configuration::supportDir() . 'Step/Shire/User/Login.php',
            $generated['filename']
        );
        $this->assertIsValidPhp($this->content);
    }
}
