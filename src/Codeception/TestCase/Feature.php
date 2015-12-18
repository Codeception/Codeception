<?php
namespace Codeception\TestCase;

use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Feature extends \Codeception\Lib\Test implements
    TestCase,
    TestCase\Interfaces\ScenarioDriven,
    TestCase\Interfaces\Descriptive,
    TestCase\Interfaces\Configurable
{
    use TestCase\Shared\Actor;
    use TestCase\Shared\ScenarioPrint;

    public function test()
    {

    }

    public function preload()
    {

    }


}