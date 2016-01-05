<?php
namespace Codeception\TestCase\Interfaces;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Symfony\Component\EventDispatcher\EventDispatcher;

interface Configurable
{
    public function _services(EventDispatcher $dispatcher, ModuleContainer $moduleContainer, Di $di);
    public function _configure($config);
    public function initConfig();
} 