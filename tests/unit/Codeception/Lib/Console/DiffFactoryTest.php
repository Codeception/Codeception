<?php
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
    protected $diffFactory;

    protected function setUp()
    {
        $this->diffFactory = new DiffFactory();
    }

    public function testItCreatesMessageForComparisonFailure()
    {
        $expectedDiff = $this->getExpectedDiff();
        $failure = $this->createFailure();
        $message = $this->diffFactory->createDiff($failure);

        $this->assertEquals($expectedDiff, (string) $message, 'The diff should be generated.');
    }

    /**
     * @return ComparisonFailure
     */
    protected function createFailure()
    {
        $expected = "a\nb";
        $actual = "a\nc";

        return new ComparisonFailure($expected, $actual, $expected, $actual);
    }

    /**
     * @return string
     */
    protected function getExpectedDiff()
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
