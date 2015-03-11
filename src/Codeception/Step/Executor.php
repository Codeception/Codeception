<?php
namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;

class Executor extends \Codeception\Step
{

    protected $callable = null;

    public function __construct(\Closure $callable, $arguments = [])
    {
        // TODO: add serialization to function http://www.htmlist.com/development/extending-php-5-3-closures-with-serialization-and-reflection/
        parent::__construct('execute callable function', []);

        $this->callable = $callable;
    }

    public function run(ModuleContainer $container = null)
    {
        $callable = $this->callable;

        return $callable();
    }

}
