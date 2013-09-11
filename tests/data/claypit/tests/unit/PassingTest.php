<?php
use Codeception\Util\Stub;

class PassingTest extends \Codeception\TestCase\Test
{

    public function testMe()
    {
        $this->assertFalse(false);
    }

}