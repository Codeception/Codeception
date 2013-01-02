<?php
$I = new CliGuy($scenario);
$I->wantTo('try generate remote codecoverage xml report');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run remote --coverage --xml');
$I->seeFileFound('clover.xml','tests/data/log');

