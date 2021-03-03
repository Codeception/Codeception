<?php

declare(strict_types=1);

class TestTest extends \Codeception\Test\Unit
{
    public function testReportedInterface()
    {
        $this->assertInstanceOf(\Codeception\Test\Interfaces\Reported::class, $this);
        $this->assertSame([
            'name' => 'testReportedInterface',
            'class' => 'TestTest',
            'file' => __FILE__
        ], $this->getReportFields());
    }
}
