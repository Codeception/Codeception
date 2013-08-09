<?php

class FailingTest extends \PHPUnit_Framework_TestCase
{
    public function testMe()
    {
        $this->assertFalse(true);
    }

}