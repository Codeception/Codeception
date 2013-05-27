<?php
namespace Codeception\Step;

class Incomplete extends \Codeception\Step
{
    public function run()
    {
        throw new \PHPUnit_Framework_IncompleteTestError($this->getAction());
    }

    public function __toString()
    {
        return $this->getAction();
    }

}
