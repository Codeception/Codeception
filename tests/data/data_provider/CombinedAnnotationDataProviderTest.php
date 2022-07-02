<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

class CombinedAnnotationDataProviderTest extends TestCase
{
    /**
     * @dataProvider getData
     * @example ["foo1", 1]
     * @example ["foo2", 2]
     */
    public function testCombined($arg1, $arg2): void
    {
    }

    public function getData(): array
    {
        return [
            'abc' => ['abc', 8],
            'def' => ['def', 9],
        ];
    }
}
