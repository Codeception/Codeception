<?php
namespace Jazz;

// here you can define custom functions for TestGuy 

class TestHelper extends \Codeception\Module
{
    public function seeEquals($expected, $actual) {
        $this->assertEquals($expected, $actual);
    }
}
