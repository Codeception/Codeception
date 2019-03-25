<?php

class TryToCest
{
    /**
     * @group ignore
     */
    public function ignoreFailure(RetryTester $I)
    {
        $I->tryToFailAt(1);
    }
}