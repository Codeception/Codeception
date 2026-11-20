<?php

declare(strict_types=1);

use Codeception\Attribute\ExampleRow;
use Codeception\Attribute\Examples;
use PHPUnit\Framework\TestCase;

class ExampleRowAttributeTest extends TestCase
{
    #[ExampleRow('foo', 7)]
    public function testSingleRow($arg1, $arg2): void
    {
    }

    #[ExampleRow('foo', null)]
    #[ExampleRow('bar', 8)]
    public function testMultipleRows($arg1, $arg2): void
    {
    }

    #[ExampleRow(name: 'foo', value: 7)]
    public function testNamedArguments($name, $value): void
    {
    }

    #[ExampleRow(['a'], ['b'])]
    public function testArrayValues($arg1, $arg2): void
    {
    }

    #[Examples(['foo', 7], ['bar', 8])]
    #[ExampleRow('baz', 9)]
    public function testCombinedWithExamples($arg1, $arg2): void
    {
    }
}
