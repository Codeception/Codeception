<?php
namespace Codeception;

use Codeception\Configuration as Config;
use Codeception\Event\SuiteEvent;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Console\Output;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

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
    protected $config = [];
    protected $options;
    protected $output;
    protected $globalConfig;
    private $modules = [];

    public function __construct($config, $options)
    {
        $this->config = array_merge($this->config, $config);
        $this->options = $options;
        $this->output = new Output($options);
        $this->_initialize();
    }


    public static function getSubscribedEvents()
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

    public function receiveModuleContainer(SuiteEvent $e)
    {
        $this->modules = $e->getSuite()->getModules();
    }

    /**
     * Pass config variables that should be injected into global config.
     *
     * @param array $config
     */
    public function _reconfigure($config = [])
    {
        if (is_array($config)) {
            Config::append($config);
        }
    }

    /**
     * You can do all preparations here. No need to override constructor.
     * Also you can skip calling `_reconfigure` if you don't need to.
     */
    public function _initialize()
    {
        $this->_reconfigure(); // hook for BC only.
    }

    protected function write($message)
    {
        if (!$this->options['silent']) {
            $this->output->write($message);
        }
    }

    protected function writeln($message)
    {
        if (!$this->options['silent']) {
            $this->output->writeln($message);
        }
    }

    public function hasModule($name)
    {
        return isset($this->modules[$name]);
    }

    public function getCurrentModuleNames()
    {
        return array_keys($this->modules);
    }

    public function getModule($name)
    {
        if (!$this->hasModule($name)) {
            throw new ModuleRequireException($name, "module is not enabled");
        }
        return $this->modules[$name];
    }

    public function getTestsDir()
    {
        return Config::testsDir();
    }

    public function getLogDir()
    {
        return Config::outputDir();
    }

    public function getDataDir()
    {
        return Config::dataDir();
    }

    public function getRootDir()
    {
        return Config::projectDir();
    }

    public function getGlobalConfig()
    {
        return Config::config();
    }
}
