<?php

declare(strict_types=1);

namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Step as CodeceptionStep;

use function array_pop;
use function end;
use function is_string;
use function str_contains;
use function str_replace;

class Meta extends CodeceptionStep
{
    public function run(?ModuleContainer $container = null): void
    {
    }

    public function setTraceInfo(string $file, int $line): void
    {
        $this->file = $file;
        $this->line = $line;
    }

    public function setPrefix(string $actor): void
    {
        $this->prefix = $actor;
    }

    public function getArgumentsAsString(int $maxLength = self::DEFAULT_MAX_LENGTH): string
    {
        $backup = $this->arguments;
        $lastArg  = end($this->arguments);
        $lastArgStr   = '';
        if (is_string($lastArg) && str_contains($lastArg, "\n")) {
            $lastArgStr = "\r\n   " . str_replace("\n", "\n   ", $lastArg);
            array_pop($this->arguments);
        }
        $result              = parent::getArgumentsAsString($maxLength) . $lastArgStr;
        $this->arguments = $backup;

        return $result;
    }

    public function setFailed(bool $failed): void
    {
        $this->failed = $failed;
    }
}
