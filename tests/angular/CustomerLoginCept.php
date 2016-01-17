<?php 
$I = new AngularGuy($scenario);
$I->wantTo('check simple angularjs app');
$I->amOnPage('/');
$I->click('Customer Login');
$I->selectOption('userSelect', 'Harry Potter');
$I->see('Login', 'button');
$I->click('Login');
$I->see('Welcome Harry Potter');
$I->seeElement(['model' => 'accountNo']); // check find by model