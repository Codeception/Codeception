<?php
class CodeTest extends Codeception\TestCase\Test
{
    public function testThis()
    {
        \Codeception\Module\OrderHelper::appendToFile('C');
    }
}