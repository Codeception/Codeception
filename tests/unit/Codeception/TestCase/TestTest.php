<?php
class TestTest extends \Codeception\Test\Format\TestCase
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

    public function testReportedInterface()
    {
        $this->assertInstanceOf('\\Codeception\\Test\\Interfaces\\Reported', $this);
        $this->assertEquals(array(
            'file' => __FILE__,
            'name' => 'testReportedInterface',
            'class' => 'TestTest',
            'feature' => 'test reported interface',
        ), $this->getReportFields());
    }

}