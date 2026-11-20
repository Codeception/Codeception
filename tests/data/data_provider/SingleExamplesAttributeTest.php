<?php

declare(strict_types=1);

use Codeception\Attribute\Examples;
use PHPUnit\Framework\TestCase;

class SingleExamplesAttributeTest extends TestCase
{
    #[Examples('foo', 7)]
    public function testScalarValues($arg1, $arg2): void
    {
    }

    #[Examples('foo', null)]
    public function testNullValue($arg1, $arg2): void
    {
    }

    #[Examples(name: 'foo', value: 7)]
    public function testNamedArguments($name, $value): void
    {
    }

    #[Examples(['foo', 7], ['bar', 8])]
    public function testListOfExamples($arg1, $arg2): void
    {
    }

    #[Examples(['foo', 7])]
    public function testSingleArrayArgument($arg1, $arg2): void
    {
    }
}
