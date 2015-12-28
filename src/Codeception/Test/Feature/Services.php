<?php
namespace Codeception\Test\Feature;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Symfony\Component\EventDispatcher\EventDispatcher;

trait Services
{
    protected $di;
    protected $dispatcher;
    protected $moduleContainer;

    public function setServices(EventDispatcher $dispatcher, ModuleContainer $moduleContainer, Di $di)
    {
        $this->moduleContainer = $moduleContainer;
        $this->dispatcher = $dispatcher;
        $this->di = clone($di);
    }

    /**
     * @return Di
     */
    public function getDi()
    {
        return $this->di;
    }

    /**
     * @return EventDispatcher
     */
    public function getDispatcher()
    {
        return $this->dispatcher;
    }

    /**
     * @return ModuleContainer
     */
    public function getModuleContainer()
    {
        return $this->moduleContainer;
    }


}