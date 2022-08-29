<?php

use Codeception\Attribute\Group;

class FailCest
{
    #[Group('pass'), Group('pass1')]
    public function passNum(RetryTester $I)
    {
        $I->retry(3);
        $I->retryFailAt(3);
    }

    #[Group('fail1')]
    public function failNum(RetryTester $I)
    {
        $I->retry(2);
        $I->retryFailAt(3);
    }

    #[Group('pass2'), Group('pass')]
    public function passTime1(RetryTester $I)
    {
        $I->retry(3, 200);
        $I->retryFailFor(0.6);
    }

    #[Group('fail2')]
    public function failNum2(RetryTester $I)
    {
        $I->retry(3, 100);
        $I->retryFailFor(1);
    }
}
