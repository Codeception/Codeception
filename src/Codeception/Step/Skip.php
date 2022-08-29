<?php

declare(strict_types=1);

namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Step as CodeceptionStep;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\SkippedWithMessageException;
use PHPUnit\Runner\Version as PHPUnitVersion;

class Skip extends CodeceptionStep
{
    public function run(ModuleContainer $container = null): void
    {
        $skipMessage = $this->getAction();

        if (PHPUnitVersion::series() < 10) {
            throw new SkippedTestError($skipMessage);
        }

        throw new SkippedWithMessageException($skipMessage);
    }

    public function __toString(): string
    {
        return $this->getAction();
    }
}
