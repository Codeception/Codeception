<?php
namespace Codeception\Module;

// here you can define custom functions for CodeGuy 

class EmulateModuleHelper extends \Codeception\Module
{
    public $assertions = 0;

    public function seeEquals($expected, $actual)
    {
        \PHPUnit_Framework_Assert::assertEquals($expected, $actual);
        $this->assertions++;
    }
    
    public function seeFeaturesEquals($expected) {
        \PHPUnit_Framework_Assert::assertEquals($expected, $this->feature);
    }

    public function _before(\Codeception\TestInterface $test) {
        $this->feature = $test->getMetadata()->getFeature();
    }
}
