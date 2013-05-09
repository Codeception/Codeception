<?php

class CestTest extends PHPUnit_Framework_TestCase
{

    public function testFilename()
    {
        $cest = \Codeception\Util\Stub::make('\Codeception\TestCase\Cest', array(
                'getTestClass' => new \Codeception\Util\Locator(),
                'getTestMethod' => 'combine'
        ));
        $this->assertEquals('Locator.combine', $cest->getFileName());
    }

}
