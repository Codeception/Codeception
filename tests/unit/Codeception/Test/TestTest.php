<?php
class TestTest extends \Codeception\Test\Unit
{

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