<?php

use Codeception\Attribute\DataProvider;

final class DataProvidersExceptionCest
{
    #[DataProvider('triangles')]
    public function testIsTriangle(UnitTester $I)
    {
        $I->amGoingTo("Fail with an exception before I even get here");
    }

    // The test of this relies upon the line numbers being unchanged. If you do need to add lines
    // please change the relevant test in tests/cli/RunCest:runTestWithDataProvidersExceptionStderrVerbose
    public function triangles()
    {
        throw new Exception("Something went wrong!!!");
    }
}
