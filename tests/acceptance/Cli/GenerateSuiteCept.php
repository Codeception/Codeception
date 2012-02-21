<?php
$I = new CliGuy($scenario);
$I->am('developer who likes testing');
$I->wantTo('generate sample Suite');
$I->lookForwardTo('have a better tests categorization');

$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:suite house HouseGuy');
$I->seeFileFound('house.suite.yml');
$I->expect('guy class is generated');
$I->seeInThisFile('class_name: HouseGuy');
$I->seeFileFound('HouseHelper.php');
$I->seeFileFound('_bootstrap.php','tests/house');
