<?php
$I = new OrderGuy($scenario);
$I->wantTo('write a marker to file to determine loading order');
$I->appendToFile('T');
