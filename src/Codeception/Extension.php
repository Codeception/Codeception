<?php

declare(strict_types=1);

namespace Codeception;

use Codeception\Configuration as Config;
use Codeception\Event\SuiteEvent;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Console\Output;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function array_keys;
use function array_merge;
use function is_array;

/**
 * A base class for all Codeception Extensions and GroupObjects
 *
 * Available Properties:
 *
 * * config: current extension configuration
 * * options: passed running options
 *
 */
abstract class Extension implements EventSubscriberInterface
{
    /**
     * @var array
     */
    protected $config = [];
    /**
     * @var array
     */
    protected $options = [];
    /**
     * @var Output
     */
    protected $output;
    /**
     * @var array
     */
    protected $globalConfig = [];
    /**
     * @var array
     */
    private $modules = [];

    public function __construct(array $config, array $options)
    {
        $this->config = array_merge($this->config, $config);
        $this->options = $options;
        $this->output = new Output($options);
        $this->_initialize();
    }

    public static function getSubscribedEvents(): array
    {
        if (!isset(static::$events)) {
            return [Events::SUITE_INIT => 'receiveModuleContainer'];
        }
        if (isset(static::$events[Events::SUITE_INIT])) {
            if (!is_array(static::$events[Events::SUITE_INIT])) {
                static::$events[Events::SUITE_INIT] = [[static::$events[Events::SUITE_INIT]]];
            }
            static::$events[Events::SUITE_INIT][] = ['receiveModuleContainer'];
        } else {
            static::$events[Events::SUITE_INIT] = 'receiveModuleContainer';
        }
        return static::$events;
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
     * Also you can skip calling `_reconfigure` if you don't need to.
     */
    public function _initialize(): void
    {
        $this->_reconfigure(); // hook for BC only.
    }

    /**
     * @param string|iterable $message
     */
    protected function write($message): void
    {
        if (!$this->options['silent'] && $message) {
            $this->output->write($message);
        }
    }

    protected function writeln(string $message): void
    {
        if (!$this->options['silent'] && $message) {
            $this->output->writeln($message);
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
        return Config::testsDir();
    }

    public function getLogDir(): string
    {
        return Config::outputDir();
    }

    public function getDataDir(): string
    {
        return Config::dataDir();
    }

    public function getRootDir(): string
    {
        return Config::projectDir();
    }

    public function getGlobalConfig(): array
    {
        return Config::config();
    }
}
