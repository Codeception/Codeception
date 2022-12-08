<?php

use Codeception\Attribute\Group;

class TryToCest
{
    #[Group('ignore')]
    public function ignoreFailure(RetryTester $I)
    {
        $result = $I->tryToFailAt(1);
        $I->assertFalse($result, 'tryTo must return false on failure');
    }
}
