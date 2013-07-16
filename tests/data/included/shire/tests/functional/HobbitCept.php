<?php use Shire\TestGuy;

$I = new TestGuy($scenario);
$I->wantTo('check that hobbits can add numbers');
$I->seeEquals(5, 3+2);
