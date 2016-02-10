<?php
class DependsTest extends \Codeception\Test\Unit {

    /**
     * @group depends
     * @depends testOne
     */
    public function testTwo()
    {
        $this->assertTrue(true);
    }
    
    /**
     * @group depends
     * @depends testFour
     */
    public function testThree()
    {
        $this->assertTrue(true);
    }

    public function testFour()
    {
        $this->assertTrue(true);        
    }
    
    
    /**
     * @group depends
     */
    public function testOne()
    {
        $this->assertTrue(false);
    }

}