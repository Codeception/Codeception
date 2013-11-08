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
        $I->seeInShellOutput("IncompleteMeCept.php");
        $I->dontSeeInShellOutput("CommentsCept.php");
        $I->dontSeeInShellOutput("SkipMeCept.php");

    }

    public function runTwoSuites(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run skipped,dummy --no-exit');
        $I->seeInShellOutput("Skipped Tests");
        $I->seeInShellOutput("Dummy Tests");
        $I->dontSeeInShellOutput("Remote Tests");
    }

    public function skipSuites(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run --skip skipped --skip remote --skip remote_server --skip order --skip unit --skip powers');
        $I->seeInShellOutput("Dummy Tests");
        $I->dontSeeInShellOutput("Remote Tests");
        $I->dontSeeInShellOutput("Remote_server Tests");
        $I->dontSeeInShellOutput("Order Tests");

    }

    public function runOneTestFromUnit(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/dummy/AnotherTest.php:testFirst');
        $I->seeShellOutputMatches("~Running AnotherTest::testFirst\s*?Ok~");
        $I->dontSeeInShellOutput('AnotherTest::testSecond');
    }

    public function runOneTestFromCest(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/dummy/AnotherCest.php:optimistic');
        $I->seeShellOutputMatches("~\(AnotherCest.optimistic\)\s*?Ok~");
        $I->dontSeeInShellOutput('AnotherCest.pessimistic');
    }

    public function runTestWithDataProviders(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/unit/DataProvidersTest.php');
        $I->seeInShellOutput('Trying to test is triangle with data set "real triangle" (DataProvidersTest::testIsTriangle)');
        $I->seeInShellOutput('Trying to test is triangle with data set #0 (DataProvidersTest::testIsTriangle)');
        $I->seeInShellOutput('Trying to test is triangle with data set #1 (DataProvidersTest::testIsTriangle)');
        $I->seeInShellOutput("OK");
    }

}