<?php
$I = new CliGuy($scenario);
$I->am('developer who likes testing');
$I->wantTo('generate sample Suite');
$I->lookForwardTo('have a better tests categorization');

$I->amInPath('tests/data/sandbox');
$I->executeCommand('bootstrap --empty src/FooBar --namespace FooBar');
$I->executeCommand('generate:suite house HouseGuy -c src/FooBar');
$I->seeFileFound('house.suite.yml', 'src/FooBar/tests');
$I->expect('guy class is generated');
$I->seeInThisFile('class_name: HouseGuy');
$I->seeInThisFile('- \FooBar\Helper\HouseGuy');
$I->seeFileFound('HouseGuy.php', 'src/FooBar/tests/_support/Helper');
$I->seeInThisFile('namespace FooBar\Helper;');
$I->seeFileFound('_bootstrap.php', 'src/FooBar/tests/house');

$I->expect('suite is not created due to dashes');
$I->executeCommand('generate:suite invalid-dash-suite');
$I->seeInShellOutput('contains invalid characters');
