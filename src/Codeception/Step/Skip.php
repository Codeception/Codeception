<?php
namespace Codeception\Step;

class Skip extends \Codeception\Step
{
    public function run()
    {
        throw new \PHPUnit_Framework_SkippedTestError($this->getAction());
    }
    
    public function __toString()
    {
        return $this->getAction();
    }    

}
