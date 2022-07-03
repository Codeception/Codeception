<?php

namespace Tests\Functional;

use Codeception\Attribute\Before;
use \Tests\Support\FunctionalTester;

class LoginCest
{
    public function _before(FunctionalTester $I)
    {
    }

    public function tryToTest(FunctionalTester $I)
    {
        $I->amOnPage('/');
    }
}
