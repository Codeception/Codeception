<?php
$I = new CoverGuy($scenario);
$I->wantTo('run local code coverage for cest and test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run math MathTest --coverage');
$I->seeInShellOutput('Classes: 100.00%');
$I->seeInShellOutput('Methods: 100.00%');

$I->amGoingTo('run local codecoverage in cest');
$I->executeCommand('run math MathCest --coverage');
$I->seeInShellOutput('Classes: 100.00%');
$I->seeInShellOutput('Methods: 100.00%');
