<?php

use Codeception\Attribute\Group;

final class AnotherCest
{
    #[Group('ok')]
    public function optimistic(DumbGuy $I)
    {
        $I->expect('everything is ok');
    }

    public function pessimistic(DumbGuy $I)
    {
        $I->expect('everything is bad');
    }
}
