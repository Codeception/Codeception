<?php
namespace Codeception\Module;

// here you can define custom functions for CoverGuy

class CoverHelper extends \Codeception\Module
{
    
    public function _before(\Codeception\TestCase $test) {
        if (floatval(phpversion()) == '5.3') $test->markTestSkipped();
    }
}
