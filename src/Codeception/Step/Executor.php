<?php
namespace Codeception\Step;

use Codeception\Step as CodeceptionStep;
use Codeception\Lib\ModuleContainer;

class Executor extends CodeceptionStep
{

    protected $callable = null;

    public function __construct(\Closure $callable, $arguments = [])
    {
        parent::__construct('execute callable function', []);

        $this->callable = $callable;
    }

    public function run(ModuleContainer $container = null)
    {
        $callable = $this->callable;

        return $callable();
    }
}
