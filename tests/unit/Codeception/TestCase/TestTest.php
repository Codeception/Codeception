<?php
class TestTest extends \Codeception\TestCase\Test
{
    public function testBaseOne()
    {
        return 'hey';
    }

    /**
     * @depends testBaseOne
     */
    public function testDependentOne($hey)
    {
        $this->assertEquals('hey', $hey);
    }

}