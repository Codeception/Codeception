<?php
class DependsTest extends \Codeception\TestCase\Test {

    public function testOne()
    {
        $this->assertTrue(FALSE);
    }


    /**
     * @depends testOne
     */
    public function testTwo()
    {
    }



}