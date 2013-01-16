<?php
$I = new CliGuy($scenario);
$I->wantTo('check xml reports');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run --xml');
$I->seeFileFound('report.xml','tests/_log');
$I->seeInThisFile('<?xml');
$I->seeInThisFile('<testsuite name="dummy"');
$I->seeInThisFile('<testcase file="FileExistsCept.php"');

if (floatval(phpversion()) == '5.3') $this->markTestSkipped();