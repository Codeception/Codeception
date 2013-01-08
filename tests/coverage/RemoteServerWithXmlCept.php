<?php
$I = new CliGuy($scenario);
$I->wantTo('try generate remote codecoverage xml report');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote_server --coverage --xml');
$I->seeFileFound('remote_server.remote.coverage.xml','tests/_log');
$I->seeInThisFile('coverage generated');

