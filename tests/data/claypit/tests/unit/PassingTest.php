<?php

class PassingTest extends \Codeception\Test\Format\TestCase
{

    public function testMe()
    {
        $this->assertFalse(false);
    }

}