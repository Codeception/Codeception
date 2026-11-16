<?php

use Tests\Support\CoverTester;

$I = new CoverTester($scenario);
$I->wantTo('print only the summary of local code coverage');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run math MathTest --coverage', false, '-d pcov.directory=' . getcwd());
$I->seeShellOutputMatches('#Summary:#');
$I->seeCoverageDetailsPerClass();

$I->amGoingTo('enable show_only_summary');
$I->executeCommand("run -o 'coverage: show_only_summary: true' math MathTest --coverage", false, '-d pcov.directory=' . getcwd());
$I->seeShellOutputMatches('#Summary:#');
$I->dontSeeCoverageDetailsPerClass();
