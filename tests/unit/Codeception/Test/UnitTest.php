<?php

declare(strict_types=1);

class TestTest extends \Codeception\Test\Unit
{
    public function testReportedInterface(): void
    {
        $this->assertInstanceOf(\Codeception\Test\Interfaces\Reported::class, $this);
        $this->assertEquals([
            'file' => __FILE__,
            'name' => 'testReportedInterface',
            'class' => 'TestTest'
        ], $this->getReportFields());
    }
}
