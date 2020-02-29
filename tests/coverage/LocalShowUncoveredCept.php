<?php
$I = new CoverGuy($scenario);
$I->amInPath('tests/data/sandbox');
$I->wantTo('run local code coverage for cest and test with show uncovered');
$I->executeCommand('run math2_show_uncovered MathCest:testAddition --coverage', null, '-d pcov.directory=' . getcwd());
$I->seeInShellOutput('OK (1 test');
$I->seeInShellOutput('Classes: 50.00%');
$I->seeInShellOutput('Methods: 50.00%');

$I->wantTo('run local code coverage for cest and test without show uncovered');
$I->executeCommand('run math2 MathCest:testAddition --coverage', null, '-d pcov.directory=' . getcwd());
$I->seeInShellOutput('OK (1 test');
$I->seeInShellOutput('Classes: 100.00%');
$I->seeInShellOutput('Methods: 100.00%');
