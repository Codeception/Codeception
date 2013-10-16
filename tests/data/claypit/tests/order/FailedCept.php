<?php
$scenario->group('simple');
\Codeception\Module\OrderHelper::appendToFile('S');
$I = new OrderGuy($scenario);
$I->wantTo('write a marker and fail');
$I->appendToFile('T');
$I->failNow();