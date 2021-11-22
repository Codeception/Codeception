<?php

declare(strict_types=1);

namespace Codeception\Step;

use Closure;
use Codeception\Lib\ModuleContainer;
use Codeception\Step as CodeceptionStep;

class Executor extends CodeceptionStep
{
    protected Closure $callable;

    public function __construct(Closure $callable, array $arguments = [])
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
