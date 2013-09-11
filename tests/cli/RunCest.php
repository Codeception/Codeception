<?php

class RunCest
{
    public function _before(\Codeception\Event\Test $t)
    {
        if (floatval(phpversion()) == '5.3') $t->getTest()->getScenario()->skip();
    }

    public function runOneFile(\CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/dummy/FileExistsCept.php');
        $I->seeInShellOutput("OK (");
    }

    /**
     * @group reports
     * @param CliGuy $I
     */
    public function runHtml(\CliGuy $I) {
        $I->wantTo('execute tests with html output');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run dummy --html');
        $I->seeFileFound('report.html','tests/_log');
    }

    /**
     * @group reports
     * @param CliGuy $I
     */
    public function runJsonReport(\CliGuy $I)
    {
        $I->wantTo('check json reports');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run dummy --json');
        $I->seeFileFound('report.json','tests/_log');
        $I->seeInThisFile('"suite":');
        $I->seeInThisFile('"dummy"');
    }

    /**
     * @group reports
     * @param CliGuy $I
     */
    public function runTapReport(\CliGuy $I)
    {
        $I->wantTo('check tap reports');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run dummy --tap');
        $I->seeFileFound('report.tap.log','tests/_log');
    }

    /**
     * @group reports
     * @param CliGuy $I
     */
    public function runXmlReport(\CliGuy $I)
    {
        $I->wantTo('check xml reports');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run dummy --xml');
        $I->seeFileFound('report.xml','tests/_log');
        $I->seeInThisFile('<?xml');
        $I->seeInThisFile('<testsuite name="dummy"');
        $I->seeInThisFile('<testcase file="FileExistsCept.php"');
    }

    /**
     * @group reports
     * @param CliGuy $I
     */
    public function runReportMode(\CliGuy $I)
    {
        $I->wantTo('try the reporting mode');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run dummy --report');
        $I->seeInShellOutput('FileExistsCept.php');
        $I->seeInShellOutput('........Ok');

    }

    public function runOneGroup(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run skipped -g notorun');
        $I->seeInShellOutput("Incomplete");
        $I->dontSeeInShellOutput("Skipped");

    }

    public function runTwoSuites(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run skipped,dummy --no-exit');
        $I->seeInShellOutput("Suite skipped started");
        $I->seeInShellOutput("Suite dummy started");
        $I->dontSeeInShellOutput("Suite remote started");
    }

    public function skipSuites(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run --skip skipped --skip remote --skip remote_server --skip order --skip unit');
        $I->seeInShellOutput("Suite dummy started");
        $I->dontSeeInShellOutput("Suite remote started");
        $I->dontSeeInShellOutput("Suite remote_server started");
        $I->dontSeeInShellOutput("Suite order started");

    }

    public function runOneTestFromUnit(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/dummy/AnotherTest.php:testFirst');
        $I->seeInShellOutput('Running AnotherTest::testFirst - Ok');
        $I->dontSeeInShellOutput('AnotherTest::testSecond');
    }

    public function runOneTestFromCest(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/dummy/AnotherCest.php:optimistic');
        $I->seeInShellOutput('(AnotherCest.optimistic) - Ok');
        $I->dontSeeInShellOutput('AnotherCest.pessimistic');
    }

}