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

// TODO: Xdebug 3.6 (the first version supporting PHP 8.6) segfaults with path coverage,
// remove the version check once https://bugs.xdebug.org/view.php?id=2445 is fixed
if (PHP_VERSION_ID < 80600) {
    $I->amGoingTo('run local code coverage with path and branch coverage');
    $I->executeCommand("run -o 'coverage: path_coverage: true' math MathCest --coverage", false, '-d pcov.directory=' . getcwd());
    $I->seeShellOutputMatches('#Paths:\s+66\.6(6|7)\%#');
    $I->seeShellOutputMatches('#Branches:\s+80\.00\%#');
}
