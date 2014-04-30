<?php
namespace Codeception\TestCase\Interfaces;

use Symfony\Component\EventDispatcher\EventDispatcher;

interface Configurable {

    public function configActor($actor);
    public function configDispatcher(EventDispatcher $dispatcher);
    public function config($name, $value);
    public function configEnv($env);
    public function initConfig();

} 