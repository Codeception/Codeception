<?php
\Codeception\Module\OrderHelper::appendToFile('P'); // parsed

class ParsedLoadedTest  extends \PHPUnit\Framework\TestCase
{
    public function testSomething()
    {
        \Codeception\Module\OrderHelper::appendToFile('T');
    }
}