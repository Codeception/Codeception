<?php

$I = new CoverGuy($scenario);
$I->wantTo('run local code coverage for cest and test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run math MathTest --coverage', false, '-d pcov.directory=' . getcwd());
$I->seeInShellOutput('Classes: 100.00%');
$I->seeInShellOutput('Methods: 100.00%');

$I->amGoingTo('run local codecoverage in cest');
$I->executeCommand('run math MathCest --coverage', false, '-d pcov.directory=' . getcwd());
$I->seeInShellOutput('Classes: 100.00%');
$I->seeInShellOutput('Methods: 100.00%');

$I->amGoingTo('run local code coverage with path and branch coverage');
$I->executeCommand("run -o 'coverage: path_coverage: true' math MathCest --coverage", false);
$I->seeInShellOutput('Paths:   66.67%');
$I->seeInShellOutput('Branches:   80.00%');
