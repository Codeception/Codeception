<?php
class BasicTest extends \Codeception\Test\Unit
{
    public function testMe()
    {
        $this->assertFalse(isset($this->tester));
        $this->assertTrue(true);
    }
}