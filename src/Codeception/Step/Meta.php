<?php
namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;
use Codeception\Step as CodeceptionStep;

class Meta extends CodeceptionStep
{
    public function run(ModuleContainer $container = null)
    {
    }

    public function setTraceInfo($file, $line)
    {
        $this->file = $file;
        $this->line = $line;
    }

    public function setPrefix($actor)
    {
        $this->prefix = $actor;
    }

    public function getArgumentsAsString($maxLength = 200)
    {
        $argumentBackup = $this->arguments;
        $lastArgAsString = '';
        $lastArg = end($this->arguments);
        if (is_string($lastArg) && strpos($lastArg, "\n")  !== false) {
            $lastArgAsString = "\r\n   " . str_replace("\n", "\n   ", $lastArg);
            array_pop($this->arguments);
        }
        $result = parent::getArgumentsAsString($maxLength) . $lastArgAsString;
        $this->arguments = $argumentBackup;
        return $result;
    }

    public function setFailed($failed)
    {
        $this->failed = $failed;
    }
}
