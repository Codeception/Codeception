<?php
$I = new CliGuy($scenario);
$I->wantTo('generate xml reports for unit tests');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run unit --xml --no-exit');
$I->seeFileFound('report.xml','tests/_log');
$I->seeInThisFile('<?xml');
$I->seeInThisFile('<testsuite name="unit" tests="5" assertions="5" failures="1" errors="0"');
$I->seeInThisFile('<testcase name="testMe" class="FailingTest"');
$I->seeInThisFile('<failure type="PHPUnit_Framework_ExpectationFailedException">FailingTest::testMe');
$I->seeInThisFile('<testcase name="testMe" class="PassingTest"');


