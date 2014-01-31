<?php
class CodeTest extends Codeception\TestCase\Test
{
    public function testThis()
    {
        \Codeception\Module\OrderHelper::appendToFile('C');
    }

    public static function setUpBeforeClass()
    {
        \Codeception\Module\OrderHelper::appendToFile('{');
    }

    public static function tearDownAfterClass()
    {
        \Codeception\Module\OrderHelper::appendToFile('}');
    }
}