<?php
$I = new AbsolutelyOtherGuy($scenario);
$I->wantTo('show message');
$I->amOnPage('/');
$I->see('Welcome to test app');