<?php
// @group simple
\Codeception\Module\OrderHelper::appendToFile('S');
$I = new OrderGuy($scenario);
$I->wantTo('write a marker, use can* functions and write a marker after the failures');
$I->appendToFile('T');
$I->canSeeFailNow();
$I->cantSeeFailNow();
$I->wantTo('ensure a second marker is written after failures');
$I->appendToFile('T');
