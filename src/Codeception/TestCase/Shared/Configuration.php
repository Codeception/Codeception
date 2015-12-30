<?php
namespace Codeception\TestCase\Shared;

use Codeception\Exception\ConfigurationException;
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
    protected $env;

    public function _services(EventDispatcher $dispatcher, ModuleContainer $moduleContainer, Di $di)
    {
        $this->dispatcher = $dispatcher;
        $this->di = clone($di);
        $this->moduleContainer = $moduleContainer;
    }

    public function _configure($config)
    {
        foreach ($config as $property => $value) {
            $this->$property = $value;
        }
        return $this;
    }
}
