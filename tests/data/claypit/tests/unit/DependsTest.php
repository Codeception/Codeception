<?php
class DependsTest extends \Codeception\Test\Format\TestCase {

    public function testOne()
    {
        $this->assertTrue(true);
        return 'hey';
    }


    /**
     * @depends testOne
     */
    public function testTwo($hey)
    {
        $this->assertEquals('hey', $hey);
    }




}