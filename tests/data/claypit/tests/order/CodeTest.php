<?php
use Codeception\Module\OrderHelper;

class CodeTest extends \Codeception\Test\Unit
{
    public function testThis()
    {
        OrderHelper::appendToFile('C');
    }

    public static function _setUpBeforeClass()
    {
        OrderHelper::appendToFile('{');
    }

    public static function _tearDownAfterClass()
    {
        OrderHelper::appendToFile('}');
    }

    /**
     * @before
     */
    public function before()
    {
        OrderHelper::appendToFile('<');
    }

    /**
     * @after
     */
    public function after()
    {
        OrderHelper::appendToFile('>');
    }

    /**
     * @beforeClass
     */
    public static function beforeClass()
    {
        OrderHelper::appendToFile('{');
    }

    /**
     * @afterClass
     */
    public static function afterClass()
    {
        OrderHelper::appendToFile('}');
    }
}