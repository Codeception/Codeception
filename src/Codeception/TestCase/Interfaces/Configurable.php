<?php
namespace Codeception\TestCase\Interfaces;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Symfony\Component\EventDispatcher\EventDispatcher;

interface Configurable
{
    public function configActor($actor);

    public function configDispatcher(EventDispatcher $dispatcher);

    public function configModules(ModuleContainer $moduleContainer);

    public function configDi(Di $di);

    public function config($name, $value);

    public function configEnv($env);

    public function initConfig();
} 