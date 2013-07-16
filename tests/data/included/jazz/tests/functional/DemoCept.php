<?php use Jazz\TestGuy;

$I = new TestGuy($scenario);
$I->wantTo('check that jazz musicians can add numbers');
$I->seeEquals(10,3+7);
