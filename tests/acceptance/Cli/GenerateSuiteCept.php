<?php
$I = new CliGuy($scenario);
$I->wantTo('generate sample Suite');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:suite house HouseGuy');
$I->seeFileFound('house.suite.yml');
$I->seeInThisFile('class_name: HouseGuy');
$I->seeFileFound('HouseHelper.php');
$I->seeFileFound('_bootstrap.php','tests/house');
