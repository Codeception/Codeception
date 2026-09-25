<?php

use Tests\Support\CoverTester;

$I = new CoverTester($scenario);
$I->wantTo('run local code coverage for cest and test');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run math MathTest --coverage', false, '-d pcov.directory=' . getcwd());
$I->seeShellOutputMatches('#Classes:\s+100\.00\%#');
$I->seeShellOutputMatches('#Methods:\s+100\.00\%#');

$I->amGoingTo('run local codecoverage in cest');
$I->executeCommand('run math MathCest --coverage', false, '-d pcov.directory=' . getcwd());
$I->seeShellOutputMatches('#Classes:\s+100\.00\%#');
$I->seeShellOutputMatches('#Methods:\s+100\.00\%#');

$I->amGoingTo('run local code coverage with path and branch coverage');
$I->executeCommand("run -o 'coverage: path_coverage: true' math MathCest --coverage", false);
$I->seeShellOutputMatches('#Paths:\s+66\.67\%#');
$I->seeShellOutputMatches('#Branches:\s+80\.00\%#');
