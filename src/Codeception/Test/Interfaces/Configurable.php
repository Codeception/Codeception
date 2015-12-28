<?php
namespace Codeception\Test\Interfaces;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Symfony\Component\EventDispatcher\EventDispatcher;

interface Configurable
{
    public function setServices(EventDispatcher $dispatcher, ModuleContainer $moduleContainer, Di $di);

}