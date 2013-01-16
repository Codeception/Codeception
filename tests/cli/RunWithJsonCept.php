<?php
$I = new CliGuy($scenario);
$I->wantTo('check json reports');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run --json');
$I->seeFileFound('report.json','tests/_log');
$I->seeInThisFile('"suite":');
$I->seeInThisFile('"dummy"');

if (floatval(phpversion()) == '5.3') $this->markTestSkipped();