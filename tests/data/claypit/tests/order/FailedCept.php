<?php
$I = new OrderGuy($scenario);
$I->wantTo('write a marker and fail');
$I->appendToFile('T');
$I->fail();