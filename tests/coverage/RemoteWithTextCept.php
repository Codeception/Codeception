<?php
$I = new CoverGuy($scenario);
$I->wantTo('try generate remote codecoverage text report');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote --coverage-text');
$I->seeFileFound('coverage.txt','tests/_output');
$I->seeCoverageStatsNotEmpty();

