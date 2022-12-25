<?php

namespace Unit;

use Codeception\Attribute\Skip;
use UnitTester;

class MethodLevelSkipAttributeWithoutMessageCest
{
    public function method1(UnitTester $I)
    {
    }

    #[Skip]
    public function method2(UnitTester $I)
    {
    }
}
