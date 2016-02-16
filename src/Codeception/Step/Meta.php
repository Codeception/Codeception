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

    protected function getArgumentsAsString(array $arguments)
    {
        $lastArgAsString = '';
        $lastArg = end($arguments);
        if (is_string($lastArg) && strpos($lastArg, "\n")  !== false) {
            $lastArgAsString = "\r\n   " . str_replace("\n", "\n   ", $lastArg);
            array_pop($arguments);
        }
        return parent::getArgumentsAsString($arguments) . $lastArgAsString;
    }
}
