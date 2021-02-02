<?php

declare(strict_types=1);

namespace Codeception\Step;

use Closure;
use Codeception\Step as CodeceptionStep;
use Codeception\Lib\ModuleContainer;

class Executor extends CodeceptionStep
{
    /**
     * @var Closure
     */
    protected $callable;

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
