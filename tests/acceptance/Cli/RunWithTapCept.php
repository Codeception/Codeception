<?php
$I = new CliGuy($scenario);
$I->wantTo('check tap reports');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run --tap');
$I->seeFileFound('report.tap.log','tests/_log');
$I->seeInThisFile('ok 1 - check config exists');