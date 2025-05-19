<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Event\SuiteEvent;
use Codeception\Exception\ModuleRequireException;
use Codeception\Extension\SuiteInitSubscriberTrait;
use Codeception\Lib\Console\Output;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

use function array_keys;
use function array_merge;

/**
 * A base class for all Codeception Extensions and GroupObjects
 *
 * Available Properties:
 *
 * * config: current extension configuration
 * * options: passed running options
 */
abstract class Extension implements EventSubscriberInterface
{
    use SuiteInitSubscriberTrait;

    /**
     * @var array<int|string, mixed>
     */
    protected array $config = [];

    protected Output $output;

    protected array $globalConfig = [];

    /**
     * @var array<string, Module>
     */
    private array $modules = [];

    public function __construct(array $config, protected array $options)
    {
        $this->config = array_merge($this->config, $config);
        $this->output = new Output($options);
        $this->_initialize();
    }

    public function receiveModuleContainer(SuiteEvent $event): void
    {
        $this->modules = $event->getSuite()->getModules();
    }

    /**
     * Pass config variables that should be injected into global config.
     */
    public function _reconfigure(array $config = []): void
    {
        Configuration::append($config);
    }

    /**
     * You can do all preparations here. No need to override constructor.
     * Also, you can skip calling `_reconfigure` if you don't need to.
     */
    public function _initialize(): void
    {
        $this->_reconfigure(); // hook for BC only.
    }

    /**
     * @param string|iterable $messages The message as an iterable of strings or a single string
     */
    protected function write(iterable|string $messages): void
    {
        if (empty($this->options['silent']) && $messages) {
            $this->output->write($messages);
        }
    }

    /**
     * @param string|iterable $messages The message as an iterable of strings or a single string
     */
    protected function writeln(iterable|string $messages): void
    {
        if (empty($this->options['silent']) && $messages) {
            $this->output->writeln($messages);
        }
    }

    public function hasModule(string $name): bool
    {
        return isset($this->modules[$name]);
    }

    /**
     * @return string[]
     */
    public function getCurrentModuleNames(): array
    {
        return array_keys($this->modules);
    }

    public function getModule(string $name): Module
    {
        if (!$this->hasModule($name)) {
            throw new ModuleRequireException($name, 'module is not enabled');
        }
        return $this->modules[$name];
    }

    public function getTestsDir(): string
    {
        return Configuration::testsDir();
    }

    public function getLogDir(): string
    {
        return Configuration::outputDir();
    }

    public function getDataDir(): string
    {
        return Configuration::dataDir();
    }

    public function getRootDir(): string
    {
        return Configuration::projectDir();
    }

    public function getGlobalConfig(): array
    {
        return Configuration::config();
    }
}
