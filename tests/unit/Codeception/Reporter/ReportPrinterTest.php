<?php

namespace Codeception\Reporter;

use Codeception\Attribute\DataProvider;
use Codeception\Test\Unit;
use Codeception\Event\FailEvent;
use Codeception\Test\Test;

class ReportPrinterTest extends Unit
{
    #[DataProvider('percentTestNamesProvider')]
    public function testPercentInTestNameDoesNotThrowException(string $testName)
    {
        $printer = new ReportPrinter([]);

        // Use a dummy test object that will return a name with % whenDescriptor::getTestAsString is called
        $test = $this->createMock(Test::class);
        $test->method('toString')->willReturn($testName);

        // This will trigger printTestResult internally
        $event = new FailEvent($test, new \Exception(), 1.0);

        // If the %% escaping works, testError will not throw ArgumentCountError
        $printer->testError($event);

        // We assert true just to ensure it reaches here without exception
        $this->assertTrue(true);
    }

    public function percentTestNamesProvider(): array
    {
        return [
            ['testWith100%Coverage'],
            ['%'],
            ['there are %%% from %%% cases solved'],
            ['100%%'],
            ['string with %s format'],
            ['string with %d numbers'],
            ['string with %f floats'],
            ['string with %x %c %o %b'],
        ];
    }
}
