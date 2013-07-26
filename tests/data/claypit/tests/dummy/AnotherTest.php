<?php
class AnotherTest extends PHPUnit_Framework_TestCase
{
    public function testFirst() {
        $this->assertTrue(true);
    }

    public function testSecond()
    {
        $this->assertFalse(false);
    }
    
}