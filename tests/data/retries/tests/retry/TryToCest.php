<?php

use Codeception\Attribute\Group;

class TryToCest
{
    #[Group('ignore')]
    public function ignoreFailure(RetryTester $I)
    {
        $I->tryToFailAt(1);
    }
}
