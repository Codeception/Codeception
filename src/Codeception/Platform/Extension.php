<?php
namespace Codeception\Platform;

use Codeception\Exception\ModuleRequire;
use Codeception\Util\Console\Output;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Extension implements EventSubscriberInterface {
    
    protected $config;
    protected $options;
    protected $output;

    function __construct($config, $options)
    {
        if (isset($config['extensions']['config'][get_class($this)]))
            $this->config = $config['extensions']['config'][get_class($this)];

        $this->options = $options;
        $this->output = new Output($options);
        $this->_reconfigure();
    }

    static $events = array();

    public function _reconfigure()
    {
    }

    protected function write($message)
    {
        if (!$this->options['silent']) $this->output->write($message);
    }

    protected function writeln($message)
    {
        if (!$this->options['silent']) $this->output->writeln($message);
    }

    public function getModule($name)
    {
        if (!isset(\Codeception\SuiteManager::$modules[$name])) 
            throw new ModuleRequire($name, "module is not enabled");
        return \Codeception\SuiteManager::$modules[$name];
    }

    static function getSubscribedEvents()
    {
        return static::$events;
    }

}