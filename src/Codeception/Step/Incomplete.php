<?php
namespace Codeception\Step;

use Codeception\Step as CodeceptionStep;
use Codeception\Lib\ModuleContainer;

class Incomplete extends CodeceptionStep
{
    public function run(ModuleContainer $container = null)
    {
        throw new \PHPUnit\Framework\IncompleteTestError($this->getAction());
    }

    public function __toString()
    {
        return $this->getAction();
    }
}
