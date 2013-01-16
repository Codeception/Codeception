<?php

$I = new CliGuy($scenario);
$I->wantTo('check xml reports');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run --html');
$I->seeFileFound('report.html','tests/_log');

if (floatval(phpversion()) == '5.3') $this->markTestSkipped();