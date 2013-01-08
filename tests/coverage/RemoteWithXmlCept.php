<?php
$I = new CoverGuy($scenario);
$I->wantTo('try generate remote codecoverage xml report');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote --coverage --xml');
$I->seeInShellOutput('Code Coverage Report');
$I->seeInShellOutput('Methods: 100.00% ( 1/ 1)');
$I->seeFileFound('coverage.xml','tests/_log');

