<?php
class WarningTest extends \Codeception\Test\Unit
{
    public function testWarning()
    {
        throw new \PHPUnit\Framework\Warning();
    }
}
