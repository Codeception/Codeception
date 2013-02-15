<?php
$I = new CliGuy($scenario);
$I->wantTo('generate sample Cest');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:cest dummy DummyClass');
$I->seeFileFound('DummyClassCest.php');
$I->seeInThisFile('class DummyClassCest');

