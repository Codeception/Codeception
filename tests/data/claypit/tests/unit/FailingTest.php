<?php

class FailingTest extends \PHPUnit\Framework\TestCase
{
    public function testMe()
    {
        $this->assertFalse(true);
    }

}