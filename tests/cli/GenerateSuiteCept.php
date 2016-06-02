<?php
$I = new CliGuy($scenario);
$I->am('developer who likes testing');
$I->wantTo('generate sample Suite');
$I->lookForwardTo('have a better tests categorization');

$I->amInPath('tests/data/sandbox');
$I->executeCommand('generate:suite house HouseGuy');
$I->seeFileFound('house.suite.yml', 'tests');
$I->expect('guy class is generated');
$I->seeInThisFile('class_name: HouseGuy');
$I->seeInThisFile('- \Helper\House');
$I->seeFileFound('House.php', 'tests/_support/Helper');
$I->seeInThisFile('namespace Helper;');
$I->seeFileFound('_bootstrap.php', 'tests/house');

$I->expect('suite is not created due to dashes');
$I->executeCommand('generate:suite invalid-dash-suite');
$I->seeInShellOutput('contains invalid characters');
