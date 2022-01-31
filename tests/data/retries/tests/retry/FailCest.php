<?php 

class FailCest
{
    /**
     * @group pass
     * @group pass1
     */
    public function passNum(RetryTester $I)
    {
        $I->retry(3);
        $I->retryFailAt(3);
    }

    /**
     * @group fail1
     */
    public function failNum(RetryTester $I)
    {
        $I->retry(2);
        $I->retryFailAt(3);
    }

    /**
     * @group pass2
     * @group pass
     */
    public function passTime1(RetryTester $I)
    {
        $I->retry(3, 200);
        $I->retryFailFor(0.6);
    }


    /**
     * @group fail2
     */
    public function failNum2(RetryTester $I)
    {
        $I->retry(3, 100);
        $I->retryFailFor(1);
    }

}
