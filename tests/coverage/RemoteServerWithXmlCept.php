<?php
$I = new CoverGuy($scenario);
$I->wantTo('try generate remote codecoverage xml report');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote_server --coverage-xml remote_server.xml');
$I->seeFileFound('remote_server.xml','tests/_log');
$I->seeInThisFile('coverage generated');

