<?php

class FailingTest extends \PHPUnit\Framework\TestCase
{
    public function testMe()
    {
        $this->assertFalse(true);
    }

    public function testMeTwo()
    {
        $this->assertFalse(true);
    }

    public function testMeThree()
    {
        $this->assertFalse(true);
    }
}