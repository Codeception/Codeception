<?php

class RunCest
{
    public function _before(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
    }

    public function runOneFile(\CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->executeCommand('run tests/dummy/FileExistsCept.php');
        $I->seeInShellOutput("OK (");
    }

    /**
     * @group reports
     * @group core
     *
     * @param CliGuy $I
     */
    public function runHtml(\CliGuy $I)
    {
        $I->wantTo('execute tests with html output');
        $I->executeCommand('run dummy --html');
        $I->seeFileFound('report.html', 'tests/_log');
    }

    /**
     * @group reports
     *
     * @param CliGuy $I
     */
    public function runJsonReport(\CliGuy $I)
    {
        $I->wantTo('check json reports');
        $I->executeCommand('run dummy --json');
        $I->seeFileFound('report.json', 'tests/_log');
        $I->seeInThisFile('"suite":');
        $I->seeInThisFile('"dummy"');
    }

    /**
     * @group reports
     *
     * @param CliGuy $I
     */
    public function runTapReport(\CliGuy $I)
    {
        $I->wantTo('check tap reports');
        $I->executeCommand('run dummy --tap');
        $I->seeFileFound('report.tap.log', 'tests/_log');
    }

    /**
     * @group reports
     *
     * @param CliGuy $I
     */
    public function runXmlReport(\CliGuy $I)
    {
        $I->wantTo('check xml reports');
        $I->executeCommand('run dummy --xml');
        $I->seeFileFound('report.xml', 'tests/_log');
        $I->seeInThisFile('<?xml');
        $I->seeInThisFile('<testsuite name="dummy"');
        $I->seeInThisFile('<testcase name="FileExists"');
    }

    /**
     * @group reports
     *
     * @param CliGuy $I
     */
    public function runReportMode(\CliGuy $I)
    {
        $I->wantTo('try the reporting mode');
        $I->executeCommand('run dummy --report');
        $I->seeInShellOutput('FileExistsCept.php');
        $I->seeInShellOutput('........Ok');
    }

    public function runOneGroup(\CliGuy $I)
    {
        $I->executeCommand('run skipped -g notorun');
        $I->seeInShellOutput("IncompleteMeCept.php");
        $I->dontSeeInShellOutput("SkipMeCept.php");
    }

    public function skipRunOneGroup(\CliGuy $I)
    {
        $I->executeCommand('run skipped --skip-group notorun');
        $I->seeInShellOutput("SkipMeCept.php");
        $I->dontSeeInShellOutput("IncompleteMeCept.php");
    }

    public function runTwoSuites(\CliGuy $I)
    {
        $I->executeCommand('run skipped,dummy --no-exit');
        $I->seeInShellOutput("Skipped Tests");
        $I->seeInShellOutput("Dummy Tests");
        $I->dontSeeInShellOutput("Remote Tests");
    }

    public function skipSuites(\CliGuy $I)
    {
        $I->executeCommand(
          'run --skip skipped --skip remote --skip remote_server --skip order --skip unit --skip powers'
        );
        $I->seeInShellOutput("Dummy Tests");
        $I->dontSeeInShellOutput("Remote Tests");
        $I->dontSeeInShellOutput("Remote_server Tests");
        $I->dontSeeInShellOutput("Order Tests");
    }

    public function runOneTestFromUnit(\CliGuy $I)
    {
        $I->executeCommand('run tests/dummy/AnotherTest.php:testFirst');
        $I->seeShellOutputMatches("~Running AnotherTest::testFirst\s*?Ok~");
        $I->dontSeeInShellOutput('AnotherTest::testSecond');
    }

    public function runOneTestFromCest(\CliGuy $I)
    {
        $I->executeCommand('run tests/dummy/AnotherCest.php:optimistic');
        $I->seeShellOutputMatches("~\(AnotherCest::optimistic\)\s*?Ok~");
        $I->dontSeeInShellOutput('AnotherCest::pessimistic');
    }

    public function runTestWithDataProviders(\CliGuy $I)
    {
        $I->executeCommand('run tests/unit/DataProvidersTest.php');
        $I->seeInShellOutput(
          'Trying to test is triangle with data set "real triangle" (DataProvidersTest::testIsTriangle)'
        );
        $I->seeInShellOutput('Trying to test is triangle with data set #0 (DataProvidersTest::testIsTriangle)');
        $I->seeInShellOutput('Trying to test is triangle with data set #1 (DataProvidersTest::testIsTriangle)');
        $I->seeInShellOutput("OK");
    }

    public function runWithCustomOuptutPath(\CliGuy $I)
    {
        $I->executeCommand('run dummy --xml myverycustom.xml --html myownhtmlreport.html');
        $I->seeFileFound('myverycustom.xml', 'tests/_log');
        $I->seeInThisFile('<?xml');
        $I->seeInThisFile('<testsuite name="dummy"');
        $I->seeInThisFile('<testcase name="FileExists"');
        $I->seeFileFound('myownhtmlreport.html', 'tests/_log');
        $I->dontSeeFileFound('report.xml','tests/_log');
        $I->dontSeeFileFound('report.html','tests/_log');

    }
}
