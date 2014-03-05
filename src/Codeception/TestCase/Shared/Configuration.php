<?php
namespace Codeception\TestCase\Shared;

use Codeception\Exception\Configuration as ConfigurationException;
use Codeception\Lib\Parser;
use Codeception\Scenario;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

trait Configuration
{
    /**
     * @var EventDispatcher
     */
    protected $dispatcher;
    protected $actor;
    protected $name;
    protected $testFile;
    protected $bootstrap;

    /**
     * @var Scenario
     */
    protected $scenario;

    /**
     * @var \Codeception\Lib\Parser
     */
    protected $parser;

    public function configActor($actor)
    {
        $this->actor = $actor;
        return $this;
    }

    public function configDispatcher(EventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
        return $this;
    }

    public function configFile($file)
    {
        if (!is_file($file)) {
            throw new ConfigurationException("Test file $file not found");
        }

        $this->testFile = $file;
        return $this;
    }

    public function configName($name)
    {
        $this->name = $name;
        return $this;
    }

    public function configBootstrap($bootstrap)
    {
        if ($bootstrap and !is_file($bootstrap)) {
            throw new ConfigurationException("Bootstrap file $bootstrap can't be loaded");
        }
        $this->bootstrap = $bootstrap;
        return $this;
    }

    public function config($property, $value)
    {
        $this->$property = $value;
        return $this;
    }

    public function initConfig()
    {
        $this->scenario  = new Scenario($this);
        $this->parser    = new Parser($this->scenario);
        return $this;
    }

    protected function fire($event, Event $eventType)
    {
        foreach ($this->scenario->getGroups() as $group) {
            $this->dispatcher->dispatch($event . '.' . $group, $eventType);
        }
        $this->dispatcher->dispatch($event, $eventType);
    }

    /**
     * @return \Codeception\Scenario
     */
    public function getScenario()
    {
        return $this->scenario;
    }


}