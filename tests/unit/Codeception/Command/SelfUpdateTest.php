<?php
require_once __DIR__ . DIRECTORY_SEPARATOR . 'BaseCommandRunner.php';

class SelfUpdateTest extends BaseCommandRunner
{
    const COMMAND_CLASS = '\Codeception\Command\SelfUpdate';

    public function testHasUpdate()
    {
        $this->setUpCommand('2.1.2', ['2.1.0-beta', '2.1.2', '2.1.3', '2.2.0-RC2']);
        $this->execute();

        $this->assertContains('Codeception version 2.1.2', $this->output);
        $this->assertContains('A newer version is available: 2.1.3', $this->output);
    }
    
    public function testAlreadyLatest()
    {
        $this->setUpCommand('2.1.8', ['2.1.0-beta', '2.1.7', '2.1.8', '2.2.0-RC2']);
        $this->execute();

        $this->assertContains('Codeception version 2.1.8', $this->output);
        $this->assertContains('You are already using the latest version.', $this->output);
    }

    /**
     * @param string $version
     * @param array $tags
     */
    protected function setUpCommand($version, $tags)
    {
        $this->makeCommand(
            self::COMMAND_CLASS,
            false,
            [
                'getCurrentVersion' => function () use ($version) {
                    return $version;
                },
                'getGithubTags' => function () use ($tags) {
                    return $tags;
                }
            ]
        );
        $this->config = [];
    }
}
