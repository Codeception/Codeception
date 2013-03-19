<?php
namespace Codeception\Platform;

use Codeception\Exception\ModuleRequire;
use Codeception\Output;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Extension implements EventSubscriberInterface {
    
    protected $config;
    protected $options;
    protected $output;

    function __construct($config, $options)
    {
        $this->config = $config;
        $this->options = $options;
        $this->output = new Output($options['colors']);
    }

    static function events()
    {
        return array();
    }

    protected function write($message)
    {
        if (!$this->options['silent']) $this->output->put($message);
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
        return self::events();
    }

}