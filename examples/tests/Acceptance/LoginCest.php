<?php

namespace Tests\Acceptance;

use \Tests\Support\AcceptanceTester;

class LoginCest
{
    public function tryToTest(AcceptanceTester $I)
    {
        $I->amOnPage('/');
    }
}
