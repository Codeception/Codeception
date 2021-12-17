<?php

/**
 * @group multiple-fail
 */
class MultipleFailingTest extends \PHPUnit\Framework\TestCase
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