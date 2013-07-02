<?php use Jazz\Pianist\Codeception\TestGuy;

$I = new TestGuy($scenario);
$I->wantTo('check that jazz pianists can add numbers');
$I->seeEquals(15,8+7);
