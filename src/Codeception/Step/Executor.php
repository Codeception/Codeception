<?php

declare(strict_types=1);

namespace Codeception\Step;

use Closure;
use Codeception\Lib\ModuleContainer;
use Codeception\Step as CodeceptionStep;

class Executor extends CodeceptionStep
{
    public function __construct(protected Closure $callable, array $arguments = [])
    {
        parent::__construct('execute callable function', []);
    }

    public function run(?ModuleContainer $container = null)
    {
        $callable = $this->callable;

        return $callable();
    }
}
