<?php

declare(strict_types=1);

namespace Codeception\Step;

use Codeception\Step as CodeceptionStep;
use Codeception\Lib\ModuleContainer;
use PHPUnit\Framework\SkippedTestError;

class Skip extends CodeceptionStep
{
    public function run(ModuleContainer $container = null): void
    {
        throw new SkippedTestError($this->getAction());
    }

    public function __toString(): string
    {
        return $this->getAction();
    }
}
