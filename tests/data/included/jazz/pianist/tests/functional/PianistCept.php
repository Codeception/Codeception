<?php use Jazz\Pianist\Codeception\TestGuy;

$I = new TestGuy($scenario);
$I->wantTo('check that jazz pianists can add numbers');
$I->seeEquals(15, Jazz\Pianist\BillEvans::add(7, 8));
