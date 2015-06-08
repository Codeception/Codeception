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

    public function config($property, $value)
    {
        $this->$property = $value;
        return $this;
    }
}
