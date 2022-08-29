<?php

// phpcs:ignoreFile PSR1.Files.SideEffects.FoundWithSymbols

\Codeception\Module\OrderHelper::appendToFile('P'); // parsed

class ParsedLoadedTest extends \PHPUnit\Framework\TestCase
{
    public function testSomething()
    {
        \Codeception\Module\OrderHelper::appendToFile('T');
    }
}
