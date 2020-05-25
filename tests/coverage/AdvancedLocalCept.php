<?php
$I = new CoverGuy($scenario);
$I->wantTo('run advanced local code coverage for cest and test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run advanced_coverage CoverageTest --coverage', null, '-d pcov.directory=' . getcwd());
$I->seeInShellOutput('Classes: 100.00%');
$I->seeInShellOutput('Methods: 100.00%');

$I->amGoingTo('run advanced  local codecoverage in cest');
$I->executeCommand('run advanced_coverage CoverageCest --coverage', null, '-d pcov.directory=' . getcwd());
$I->seeInShellOutput('Classes: 100.00%');
$I->seeInShellOutput('Methods: 100.00%');
