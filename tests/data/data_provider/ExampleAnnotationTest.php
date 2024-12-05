<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class ExampleAnnotationTest extends TestCase
{
    /**
     * @example ["foo", 5]
     * @example ["bar", 6]
     */
    public function testMultipleExamples($arg1, $arg2): void
    {
    }

    /**
     * @example ["bar", 4]
     */
    public function testSingleExample($arg1, $arg2): void
    {
    }
}
