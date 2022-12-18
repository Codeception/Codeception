<?php

namespace Unit;

use Codeception\Attribute\Skip;
use UnitTester;

#[Skip]
class ClassLevelSkipAttributeWithoutMessageCest
{
    public function method1(UnitTester $I)
    {
    }

    public function method2(UnitTester $I)
    {
    }
}
