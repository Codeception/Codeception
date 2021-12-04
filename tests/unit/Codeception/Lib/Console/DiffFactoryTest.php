<?php

declare(strict_types=1);

namespace Codeception\Lib\Console;

use SebastianBergmann\Comparator\ComparisonFailure;

/**
 * DiffFactoryTest
 **/
class DiffFactoryTest extends \Codeception\Test\Unit
{
    /**
     * @var DiffFactory
     */
    protected DiffFactory $diffFactory;

    protected function _setUp()
    {
        $this->diffFactory = new DiffFactory();
    }

    public function testItCreatesMessageForComparisonFailure()
    {
        $expectedDiff = $this->getExpectedDiff();
        $failure = $this->createFailure();
        $message = $this->diffFactory->createDiff($failure);

        $this->assertSame($expectedDiff, $message, 'The diff should be generated.');
    }

    protected function createFailure(): \SebastianBergmann\Comparator\ComparisonFailure
    {
        $expected = "a\nb";
        $actual = "a\nc";

        return new ComparisonFailure($expected, $actual, $expected, $actual);
    }

    protected function getExpectedDiff(): string
    {
        $expectedDiff = <<<TXT
@@ @@
 a
-b
+c
TXT;
        return $expectedDiff . "\n";
    }
}
