<?php

declare(strict_types=1);

namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Step as CodeceptionStep;
use PHPUnit\Framework\SkippedTestError;
use PHPUnit\Framework\SkippedWithMessageException;

class Skip extends CodeceptionStep
{
    public function run(ModuleContainer $container = null): void
    {
        $skipMessage = $this->getAction();

        if (\class_exists(SkippedWithMessageException::class)) {
            // PHPUnit 10+
            throw new SkippedWithMessageException($skipMessage);
        }

        //PHPUnit 9
        throw new SkippedTestError($skipMessage);
    }

    public function __toString(): string
    {
        return $this->getAction();
    }
}
