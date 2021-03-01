<?php

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateEnvironmentTest extends BaseCommandRunner
{
    protected function _setUp()
    {
        $this->makeCommand(\Codeception\Command\GenerateEnvironment::class);
        $this->config = [
            'class_name' => 'HobbitGuy',
            'path' => 'tests/shire',
            'paths' => ['envs' => 'tests/_envs','tests' => 'tests'],
        ];
    }

    public function testCreated()
    {
        $this->execute(['env' => 'firefox']);
        $this->assertStringContainsString('firefox config was created in tests/_envs/firefox.yml', $this->output);
        $this->assertSame('tests/_envs/firefox.yml', $this->filename);
    }

    public function testFailed()
    {
        $this->makeCommand(\Codeception\Command\GenerateEnvironment::class, false);
        $this->execute(['env' => 'firefox']);
        $this->assertStringContainsString('File tests/_envs/firefox.yml already exists', $this->output);
    }
}
