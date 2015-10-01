<?php

class PassingTest extends \Codeception\Test\TestCase
{

    public function testMe()
    {
        $this->assertFalse(false);
    }

}