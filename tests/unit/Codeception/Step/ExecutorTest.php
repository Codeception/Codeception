<?php
/**
 * Created by JetBrains PhpStorm.
 * User: korotovsky
 * Date: 17.06.13
 * Time: 21:08
 * To change this template use File | Settings | File Templates.
 */

class ExecutorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider valuesProvider
     */
    public function testRun($returnValue)
    {
        $expected = $returnValue;

        $executor = new \Codeception\Step\Executor(function() use ($returnValue) {
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
