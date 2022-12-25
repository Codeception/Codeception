<?php

namespace Unit;

use UnitTester;

class MethodLevelSkipAnnotationWithoutMessageCest
{
    public function method1(UnitTester $I)
    {
    }

    /**
     * @skip
     */
    public function method2(UnitTester $I)
    {
    }
}
