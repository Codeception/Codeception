<?php
$I = new WebGuy($scenario);
$I->wantTo('test two different browsers in one test');
$I->changeBrowser('chrome');
$I->amOnPage('/user-agent');
$I->see('Chrome');
$I->changeBrowser('firefox');
$I->amOnPage('/user-agent');
$I->see('Firefox');
