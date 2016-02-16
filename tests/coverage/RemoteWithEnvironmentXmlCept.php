<?php
$I = new CoverGuy($scenario);
$I->wantTo('try generate codecoverage xml report with environment');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote --coverage-xml --env default');
$I->seeInShellOutput('Code Coverage Report');
$I->dontSeeInShellOutput('RemoteException');
$I->seeFileFound('coverage.xml','tests/_output');
$I->seeCoverageStatsNotEmpty();

