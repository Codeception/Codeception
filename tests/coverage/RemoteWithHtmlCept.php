<?php

use Tests\Support\CoverTester;

$I = new CoverTester($scenario);
$I->wantTo('try generate remote codecoverage xml report');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote --coverage-html');
$I->seeFileFound('index.html', 'tests/_output/coverage');
$I->seeCoverageStatsNotEmpty();
