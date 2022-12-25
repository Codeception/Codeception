<?php

namespace Unit;

use UnitTester;

class MethodLevelSkipAnnotationWithMessageCest
{
    public function method1(UnitTester $I)
    {
    }

    /**
     * @skip Skip message
     */
    public function method2(UnitTester $I)
    {
    }
}
