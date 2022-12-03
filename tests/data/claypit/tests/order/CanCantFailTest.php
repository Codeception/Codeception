<?php

namespace Order;

use OrderGuy;

class CanCantFailTest extends \Codeception\Test\Unit
{
    protected OrderGuy $tester;

    public function testOne()
    {
        $I = $this->tester;
        $I->appendToFile('T');
        $I->canSeeFailNow();
        $I->appendToFile('T');
    }

    public function testTwo()
    {
        $I = $this->tester;
        $I->appendToFile('T');
        $I->canSeeFailNow();
        $I->appendToFile('T');
    }
}
