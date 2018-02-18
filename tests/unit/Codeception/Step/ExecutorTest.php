<?php

class ExecutorTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider valuesProvider
     */
    public function testRun($returnValue)
    {
        $expected = $returnValue;

        $executor = new \Codeception\Step\Executor(function () use ($returnValue) {
            return $returnValue;
        });
        $actual = $executor->run();

        $this->assertEquals($expected, $actual);
    }

    /**
     * @return array
     */
    public function valuesProvider()
    {
        return array(
            array(true),
            array(false),
        );
    }
}
