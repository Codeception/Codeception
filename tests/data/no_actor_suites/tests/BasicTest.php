<?php
class BasicTest extends \Codeception\Test\Unit
{
    public function testMe()
    {
        $this->assertObjectNotHasAttribute('tester', $this);
        $this->assertTrue(true);
    }
}
