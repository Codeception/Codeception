<?php
namespace Codeception\Module;

// here you can define custom functions for CoverGuy

class CoverHelper extends \Codeception\Module
{
    
    public function _before(\Codeception\TestCase $test) {
        if (strpos(PHP_VERSION, '5.3')===0) $test->markTestSkipped();
    }
}
