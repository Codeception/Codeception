<?php
use Codeception\Util\Stub;
use Codeception\Application;
use Symfony\Component\Console\Tester\CommandTester;

class BaseCommandRunner extends \Codeception\PHPUnit\TestCase
{

    /**
     * @var \Codeception\Command\Base
     */
    protected $command;

    public $filename = "";
    public $content = "";
    public $output = "";
    public $config = [];
    public $saved = [];

    protected $commandName = 'do:stuff';

    protected function execute($args = [], $isSuite = true)
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
            'createFile' => function ($file, $output) use ($self, $saved) {
                if (!$saved) {
                    return false;
                }
                $self->filename = $file;
                $self->content = $output;
                $self->log[] = ['filename' => $file, 'content' => $output];
                $self->saved[$file] = $output;
                return true;
            },
            'getGlobalConfig' => function () use ($self) {
                return $self->config;
            },
            'getSuiteConfig'  => function () use ($self) {
                return $self->config;
            },
            'createDirectoryFor' => function ($path, $testName) {
                $path = rtrim($path, DIRECTORY_SEPARATOR);
                $testName = str_replace(['/', '\\'], [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR], $testName);
                return pathinfo($path . DIRECTORY_SEPARATOR . $testName, PATHINFO_DIRNAME) . DIRECTORY_SEPARATOR;
            },
            'getSuites'       => function () {
                return ['shire'];
            },
            'getApplication'  => function () {
                return new \Codeception\Util\Maybe;
            }
        ];
        $mockedMethods = array_merge($mockedMethods, $extraMethods);

        $this->command = Stub::construct(
            $className,
            [$this->commandName],
            $mockedMethods
        );
    }

    protected function assertIsValidPhp($php)
    {
        $temp_file = tempnam(sys_get_temp_dir(), 'CodeceptionUnitTest');
        file_put_contents($temp_file, $php);
        exec('php -l ' . $temp_file, $output, $code);
        unlink($temp_file);

        $this->assertEquals(0, $code, $php);
    }
}
