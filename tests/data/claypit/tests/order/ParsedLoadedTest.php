<?php
\Codeception\Module\OrderHelper::appendToFile('P'); // parsed

class ParsedLoadedTest  extends \PHPUnit_Framework_TestCase
{
    public function testSomething()
    {
        \Codeception\Module\OrderHelper::appendToFile('T');
    }
}