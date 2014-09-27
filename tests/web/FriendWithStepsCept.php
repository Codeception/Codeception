<?php
$I = new WebGuy($scenario);
$I->wantTo('call friend with steps to ask expert work');
$I->amOnPage('/info');
$john = $I->haveFriend('john', '\WebGuy\RootWatcherSteps');
$john->does(function (WebGuy\RootWatcherSteps $I) {
   $I->seeInRootPage('Welcome to test app!');
});
$I->seeInCurrentUrl('/info');
