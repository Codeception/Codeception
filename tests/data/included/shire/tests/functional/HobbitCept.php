<?php use Shire\TestGuy;

$I = new TestGuy($scenario);
$I->wantTo('check that hobbits can add numbers');
$I->seeEquals(5, Shire\Hobbit::add(2, 3));
