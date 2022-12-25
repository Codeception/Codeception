<?php

namespace Unit;

use Codeception\Attribute\Skip;
use UnitTester;

class MethodLevelSkipAttributeWithMessageCest
{
    #[Skip('Skip message')]
    public function method1(UnitTester $I)
    {
    }

    public function method2(UnitTester $I)
    {
    }
}
