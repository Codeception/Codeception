<?php

use Codeception\Attribute\DataProvider;

final class DataProvidersFailureCest
{
    #[DataProvider('rectangle')]
    public function testIsTriangle(UnitTester $I)
    {
        $I->amGoingTo("Fail before I get here.");
    }

    public function triangles(): array
    {
        return [
            'real triangle' => [3,4,5],
            [10,12,5],
            [7,10,15]
        ];
    }
}
