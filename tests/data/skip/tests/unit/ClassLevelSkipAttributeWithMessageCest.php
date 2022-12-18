<?php

namespace Unit;

use Codeception\Attribute\Skip;
use UnitTester;

#[Skip('Skip message')]
class ClassLevelSkipAttributeWithMessageCest
{
    public function method1(UnitTester $I)
    {
    }

    public function method2(UnitTester $I)
    {
    }
}
