<?php
$I = new CoverGuy($scenario);
$I->wantTo('try generate remote crap4j xml report');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote --coverage-crap4j');
$I->seeInShellOutput('Crap4j report generated in crap4j.xml');
$I->seeFileFound('crap4j.xml', 'tests/_output');
#$I->seeCoverageStatsNotEmpty();
