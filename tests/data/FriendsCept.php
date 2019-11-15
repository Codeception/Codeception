<?php
$I = new WebGuy($scenario);
$I->wantTo('call friends to try multi session');
$I->amOnPage('/info');
$jon = $I->haveFriend('jon');
$jon->does(function (WebGuy $I) {
    $I->amOnPage('/');
    $I->seeInCurrentUrl('/');
});
$I->seeInCurrentUrl('/info');
