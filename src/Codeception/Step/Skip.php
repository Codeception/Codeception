<?php
namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;

class Skip extends \Codeception\Step
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
