<?php
namespace Codeception\Platform;

use Codeception\Configuration as Config;
use Codeception\Exception\ModuleRequireException;
use Codeception\Lib\Console\Output;
use Codeception\Subscriber\Shared\StaticEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Extension implements EventSubscriberInterface
{
    use StaticEvents;

    protected $config = [];
    protected $options;
    protected $output;
    protected $globalConfig;

    function __construct($config, $options)
    {
        $this->config = array_merge($this->config, $config);
        $this->options = $options;
        $this->output = new Output($options);
        $this->_initialize();
    }

    static $events = [];

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
     * You can do all preperations here. No need to override constructor.
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

    public function getModule($name)
    {
        if (!isset(\Codeception\SuiteManager::$modules[$name])) {
            throw new ModuleRequireException($name, "module is not enabled");
        }
        return \Codeception\SuiteManager::$modules[$name];
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