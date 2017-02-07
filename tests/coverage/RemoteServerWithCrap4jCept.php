<?php
$I = new CoverGuy($scenario);
$I->wantTo('try generate remote codecoverage crap4j report');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote_server --coverage-crap4j remote_crap.xml');
$I->seeFileFound('remote_crap.xml', 'tests/_output');
$I->seeInThisFile('Method Crap Stats');