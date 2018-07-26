<?php
class WarningTest extends \Codeception\Test\Unit
{
    /**
     * @dataProvider dependentProvider
     */
    public function testWarningInvalidDataProvider($a)
    {
        $this->assertTrue(true);
    }
    public function dependentProvider()
    {
        throw new Exception;
    }
}
