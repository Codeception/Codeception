<?php
$I = new CliGuy($scenario);
$I->wantTo('check tap reports');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run --tap');
$I->seeFileFound('report.tap.log','tests/_log');

if (floatval(phpversion()) == '5.3') $this->markTestSkipped();