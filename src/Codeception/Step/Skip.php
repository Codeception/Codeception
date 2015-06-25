<?php
namespace Codeception\Step;

use Codeception\Step as CodeceptionStep;
use Codeception\Lib\ModuleContainer;

class Skip extends CodeceptionStep
{
    public function run(ModuleContainer $container = null)
    {
        throw new \PHPUnit_Framework_SkippedTestError($this->getAction());
    }

    public function __toString()
    {
        return $this->getAction();
    }
}
