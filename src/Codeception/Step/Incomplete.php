<?php
namespace Codeception\Step;

use Codeception\Lib\ModuleContainer;

class Incomplete extends \Codeception\Step
{
    public function run(ModuleContainer $container = null)
    {
        throw new \PHPUnit_Framework_IncompleteTestError($this->getAction());
    }

    public function __toString()
    {
        return $this->getAction();
    }

}
