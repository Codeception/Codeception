<?php

declare(strict_types=1);

use Codeception\Attribute\Examples;
use PHPUnit\Framework\TestCase;

class ExamplesAttributeTest extends TestCase
{
    #[Examples("foo", 7)]
    #[Examples("bar", 8)]
    public function testMultipleExamples($arg1, $arg2): void
    {
    }

    #[Examples("foo", 9)]
    public function testSingleExample($arg1, $arg2): void
    {
    }
}
