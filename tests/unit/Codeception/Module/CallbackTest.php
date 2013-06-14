<?php

use Codeception\Util\Stub as Stub;

class CallbackTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Module\Callback
     */
    protected $module = null;

    public function setUp()
    {
        $this->module = new \Codeception\Module\Callback();
    }

    /**
     * @dataProvider valuesProvider
     */
    public function testCallback($returnValue)
    {
        $expected = $returnValue;
        $actual = $this->module->runTimeCallback(function() use ($returnValue) {
            return $returnValue;
        });

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
