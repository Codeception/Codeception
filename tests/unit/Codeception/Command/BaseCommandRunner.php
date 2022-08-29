<?php

declare(strict_types=1);

use Codeception\Application;
use Codeception\Stub;
use Symfony\Component\Console\Tester\CommandTester;

class BaseCommandRunner extends \Codeception\PHPUnit\TestCase
{
    protected ?\PHPUnit\Framework\MockObject\MockObject $command = null;

    public string $filename = "";

    public string $content = "";

    public string $output = "";

    public array $config = [];

    public array $saved = [];

    public array $log = [];

    protected string $commandName = 'do:stuff';

    protected function execute(array $args = [], $isSuite = true)
    {
        $app = new Application();
        $app->add($this->command);

        $default = \Codeception\Configuration::$defaultConfig;
        $default['paths']['tests'] = __DIR__;

        $conf = $isSuite
            ? \Codeception\Configuration::suiteSettings('unit', $default)
            : $default;

        $this->config = array_merge($conf, $this->config);

        $commandTester = new CommandTester($app->find($this->commandName));
        $args['command'] = $this->commandName;
        $commandTester->execute($args, ['interactive' => false]);
        $this->output = $commandTester->getDisplay();
    }

    protected function makeCommand($className, $saved = true, $extraMethods = [])
    {
        if (!$this->config) {
            $this->config = [];
        }

        $self = $this;

        $mockedMethods = [
            'createFile' => function (string $file, string $output) use ($self, $saved): bool {
                if (!$saved) {
                    return false;
                }

                $self->filename = $file;
                $self->content = $output;
                $self->log[] = ['filename' => $file, 'content' => $output];
                $self->saved[$file] = $output;
                return true;
            },
            'getGlobalConfig' => fn (): array => $self->config,
            'getSuiteConfig'  => fn (): array => $self->config,
            'createDirectoryFor' => function ($path, $testName): string {
                $path = rtrim($path, DIRECTORY_SEPARATOR);
                $testName = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $testName);
                return pathinfo($path . DIRECTORY_SEPARATOR . $testName, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
            },
            'getSuites'       => fn (): array => ['shire'],
            'getApplication'  => fn (): \Codeception\Util\Maybe => new \Codeception\Util\Maybe()
        ];
        $mockedMethods = array_merge($mockedMethods, $extraMethods);

        $this->command = Stub::construct(
            $className,
            [$this->commandName],
            $mockedMethods
        );
    }

    protected function assertIsValidPhp(string $php)
    {
        $tempFile = tempnam(sys_get_temp_dir(), 'CodeceptionUnitTest');
        file_put_contents($tempFile, $php);
        exec('php -l ' . $tempFile, $output, $code);
        unlink($tempFile);

        $this->assertSame(0, $code, $php);
    }
}
