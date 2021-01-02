<?php

declare(strict_types=1);

require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class GenerateSuiteTest extends BaseCommandRunner
{
    /**
     * @var array
     */
    public array $log = [];

    /**
     * @var array<string, string>
     */
    public array $config = ['actor_suffix' => 'Guy'];

    protected function _setUp()
    {
        $this->makeCommand(\Codeception\Command\GenerateSuite::class);
    }

    public function testBasic()
    {
        $this->execute(['suite' => 'shire', 'actor' => 'Hobbit'], false);

        $configFile = $this->log[0];

        $this->assertSame(\Codeception\Configuration::projectDir().'tests/Shire.suite.yml', $configFile['filename']);
        $conf = \Symfony\Component\Yaml\Yaml::parse($configFile['content']);
        $this->assertSame('Hobbit', $conf['actor']);
        $this->assertStringContainsString('Suite shire generated', $this->output);
        $actor = $this->log[1];
        $this->assertSame(\Codeception\Configuration::supportDir().'Hobbit.php', $actor['filename']);
        $this->assertStringContainsString('class Hobbit extends \Codeception\Actor', $actor['content']);
    }

    public function testGuyWithSuffix()
    {
        $this->execute(['suite' => 'shire', 'actor' => 'HobbitTester'], false);

        $configFile = $this->log[0];
        $conf = \Symfony\Component\Yaml\Yaml::parse($configFile['content']);
        $this->assertSame('HobbitTester', $conf['actor']);
    }
}
