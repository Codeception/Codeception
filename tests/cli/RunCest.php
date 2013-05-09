<?php

class RunCest
{

    public function _before(\Codeception\Event\Test $t)
    {
        if (floatval(phpversion()) == '5.3') $t->getScenario()->skip();
    }

    public function runOneFile(\CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/dummy/FileExistsCept.php');
        $I->seeFileFound('report.html','tests/_log');
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

    public function runOneGroup(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run skipped -g notorun');
        $I->seeInShellOutput("Incomplete");
        $I->dontSeeInShellOutput("Skipped");

    }

}