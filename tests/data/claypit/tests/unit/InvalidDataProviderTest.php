<?php

class InvalidDataProviderTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dependentProvider
     */
    public function testInvalidDataProvider($a)
    {
        $this->assertTrue(true);
    }

    public function dependentProvider()
    {
        throw new Exception();
    }
}
