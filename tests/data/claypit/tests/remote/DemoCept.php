<?php
$I = new OtherGuy($scenario);
$I->amOnPage('/');
$I->see('Welcome to test app');
$I->haveFriend("friend")->does(function(OtherGuy $I) {
	$I->amOnPage('/info');
	$I->see('Lots of valuable data');
});