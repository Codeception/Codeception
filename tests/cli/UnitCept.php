<?php
$testsPath = __DIR__ . '/../';

$I = new CliGuy($scenario);
$I->wantTo('generate xml reports for unit tests');
$I->amInPath('tests/data/sandbox');
$I->executeCommand('run unit --xml --no-exit');
$I->seeFileFound('report.xml', 'tests/_output');
$I->seeInThisFile('<?xml');
$I->seeInThisFile('<testsuite name="unit"');
$I->seeInThisFile('<testcase name="testMe" class="PassingTest"');
$I->seeInThisFile('<testcase name="testIsTriangle with data set #0" class="DataProvidersTest" '.
    'file="' . realpath($testsPath . '/data/sandbox/tests/unit/DataProvidersTest.php') .'" ');
$I->seeInThisFile('<testcase name="testOne" class="DependsTest"');
$I->seeInThisFile('<failure type="PHPUnit_Framework_ExpectationFailedException">FailingTest::testMe');
