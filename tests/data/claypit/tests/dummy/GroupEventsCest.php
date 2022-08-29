<?php

use Codeception\Attribute\Group;

class GroupEventsCest
{
    #[Group('countevents')]
    public function countGroupEvents(DumbGuy $I)
    {
        $I->wantTo('affirm that Group events fire only once');
    }
}
