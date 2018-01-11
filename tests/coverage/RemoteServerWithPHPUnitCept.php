<?php

$I = new CoverGuy($scenario);
$I->wantTo('try generate remote codecoverage phpunit report');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote_server --coverage-phpunit remote_server');
$I->seeFileFound('index.xml', 'tests/_output/remote_server');
