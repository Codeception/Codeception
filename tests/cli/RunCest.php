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
        $I->seeFileFound('report.html', 'tests/_output');
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
        $I->seeFileFound('report.json', 'tests/_output');
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
        $I->seeFileFound('report.tap.log', 'tests/_output');
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
        $I->seeFileFound('report.xml', 'tests/_output');
        $I->seeInThisFile('<?xml');
        $I->seeInThisFile('<testsuite name="dummy"');
        $I->seeInThisFile('<testcase name="FileExists"');
        $I->seeInThisFile('feature="');
    }

    /**
     * @group reports
     * @param CliGuy $I
     */
    public function runXmlReportsInStrictMode(\CliGuy $I)
    {
        $I->wantTo('check xml in strict mode');
        $I->executeCommand('run dummy --xml -c codeception_strict_xml.yml');
        $I->seeFileFound('report.xml', 'tests/_output');
        $I->seeInThisFile('<?xml');
        $I->seeInThisFile('<testsuite name="dummy"');
        $I->seeInThisFile('<testcase name="FileExists"');
        $I->dontSeeInThisFile('feature="');
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
        $I->seeInShellOutput('FileExistsCept');
        $I->seeInShellOutput('........Ok');
    }

    /**
     * @group reports
     *
     * @param CliGuy $I
     */
    public function runCustomReport(\CliGuy $I)
    {
        $I->wantTo('try the reporting mode');
        $I->executeCommand('run dummy --report -c codeception_custom_report.yml');
        $I->seeInShellOutput('✔ check config exists (FileExistsCept)');
        $I->dontSeeInShellOutput('Ok');
    }

    public function runOneGroup(\CliGuy $I)
    {
        $I->executeCommand('run skipped -g notorun');
        $I->seeInShellOutput("IncompleteMeCept");
        $I->dontSeeInShellOutput("SkipMeCept");
    }

    public function skipRunOneGroup(\CliGuy $I)
    {
        $I->executeCommand('run skipped --skip-group notorun');
        $I->seeInShellOutput("SkipMeCept");
        $I->dontSeeInShellOutput("IncompleteMeCept");
    }

    public function skipGroupOfCest(\CliGuy $I)
    {
        $I->executeCommand('run dummy');
        $I->seeInShellOutput('Optimistic');
        $I->executeCommand('run dummy --skip-group ok');
        $I->seeInShellOutput('Pessimistic');
        $I->dontSeeInShellOutput('Optimistic');
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
          'run dummy --skip skipped --skip remote --skip remote_server --skip order --skip unit --skip powers --skip math --skip messages'
        );
        $I->seeInShellOutput("Dummy Tests");
        $I->dontSeeInShellOutput("Remote Tests");
        $I->dontSeeInShellOutput("Remote_server Tests");
        $I->dontSeeInShellOutput("Order Tests");
    }

    public function runOneTestFromUnit(\CliGuy $I)
    {
        $I->executeCommand('run tests/dummy/AnotherTest.php:testFirst');
        $I->seeInShellOutput("AnotherTest:testFirst");
        $I->seeInShellOutput('OK');
        $I->dontSeeInShellOutput('AnotherTest:testSecond');
    }

    public function runOneTestFromCest(\CliGuy $I)
    {
        $I->executeCommand('run tests/dummy/AnotherCest.php:optimistic');
        $I->seeInShellOutput("Optimistic");
        $I->dontSeeInShellOutput('Pessimistic');
    }

    public function runTestWithDataProviders(\CliGuy $I)
    {
        $I->executeCommand('run tests/unit/DataProvidersTest.php');
        $I->seeInShellOutput('Test is triangle | "real triangle"');
        $I->seeInShellOutput('Test is triangle | #0');
        $I->seeInShellOutput('Test is triangle | #1');
        $I->seeInShellOutput('DataProvidersTest.php');
        $I->seeInShellOutput("OK");
    }

    public function runTestWithFailFast(\CliGuy $I)
    {
        $I->executeCommand('run unit --skip-group error --no-exit');
        $I->seeInShellOutput('FailingTest:testMe');
        $I->seeInShellOutput("Test me at PassingTest");
        $I->executeCommand('run unit --fail-fast --skip-group error --no-exit');
        $I->seeInShellOutput('There was 1 failure');
        $I->dontSeeInShellOutput("PassingTest:testMe");
    }

    public function runWithCustomOuptutPath(\CliGuy $I)
    {
        $I->executeCommand('run dummy --xml myverycustom.xml --html myownhtmlreport.html');
        $I->seeFileFound('myverycustom.xml', 'tests/_output');
        $I->seeInThisFile('<?xml');
        $I->seeInThisFile('<testsuite name="dummy"');
        $I->seeInThisFile('<testcase name="FileExists"');
        $I->seeFileFound('myownhtmlreport.html', 'tests/_output');
        $I->dontSeeFileFound('report.xml','tests/_output');
        $I->dontSeeFileFound('report.html','tests/_output');
    }

    public function runTestsWithDependencyInjections(\CliGuy $I)
    {
        $I->executeCommand('run math');
        $I->seeInShellOutput('Test addition at MathCest');
        $I->seeInShellOutput('Test subtraction at MathCest');
        $I->seeInShellOutput('Test square at MathCest');
        $I->seeInShellOutput('Test all at MathTest');
        $I->seeInShellOutput('OK (');
        $I->dontSeeInShellOutput('fail');
        $I->dontSeeInShellOutput('error');
    }

    public function runErrorTest(\CliGuy $I)
    {
        $I->executeCommand('run unit ErrorTest --no-exit');
        $I->seeInShellOutput('There was 1 error');
        $I->seeInShellOutput('Array to string conversion');
        $I->seeInShellOutput('ErrorTest.php:9');
    }

    public function runTestWithException(\CliGuy $I)
    {
        $I->executeCommand('run unit ExceptionTest --no-exit -v');
        $I->seeInShellOutput('There was 1 error');
        $I->seeInShellOutput('Helllo!');
        $I->expect('Exceptions are not wrapped into ExceptionWrapper');
        $I->dontSeeInShellOutput('PHPUnit_Framework_ExceptionWrapper');
        $I->seeInShellOutput('RuntimeException');
    }

    public function runTestsWithSteps(\CliGuy $I)
    {
        $I->executeCommand('run scenario SuccessCept --steps');
        $I->seeInShellOutput(<<<EOF
Scenario --
 I am in path "."
 I see file found "scenario.suite.yml"
 PASSED
EOF
);
    }

    /**
     * @param CliGuy $I
     */
    public function runTestWithFailedScenario(\CliGuy $I, $scenario)
    {
        if (!extension_loaded('xdebug') && !defined('HHVM_VERSION')) {
            $scenario->skip("Xdebug not loaded");
        }
        $I->executeCommand('run scenario FailedCept --steps --no-exit');
        $I->seeInShellOutput(<<<EOF
Fail when file is not found at FailedCept.php
Scenario --
 I am in path "."
 I see file found "games.zip"
 FAIL
EOF
        );
        $I->expect('to see scenario trace');
        $I->seeInShellOutput(<<<EOF
Scenario Steps:

 2. \$I->seeFileFound("games.zip") at tests/scenario/FailedCept.php:5
 1. \$I->amInPath(".") at tests/scenario/FailedCept.php:4

EOF
        );
    }

    /**
     * @param CliGuy $I
     */
    public function runTestWithSubSteps(\CliGuy $I, $scenario)
    {
        if (!extension_loaded('xdebug') && !defined('HHVM_VERSION')) {
            $scenario->skip("Xdebug not loaded");
        }

        $file = "codeception\\".DIRECTORY_SEPARATOR."c3";
        $I->executeCommand('run scenario SubStepsCept --steps');
        $I->seeInShellOutput(<<<EOF
Scenario --
 I am in path "."
 I see code coverage files are present
EOF
        );
        // I split this assertion into two, because extra space is printed after "present" on HHVM
        $I->seeInShellOutput(<<<EOF
   I see file found "c3.php"
   I see file found "composer.json"
   I see in this file "$file"
EOF
        );
    }

    public function runDependentCest(CliGuy $I)
    {
        $I->executeCommand('run order DependentCest --no-exit');
        $I->seeInShellOutput('Skipped: 1');
    }

    public function runDependentTest(CliGuy $I)
    {
        $I->executeCommand('run unit DependsTest --no-exit');
        $I->seeInShellOutput('Skipped: 1');
        $I->executeCommand('run unit --no-exit');
        $I->seeInShellOutput('Skipped: 2');
    }

    public function runGherkinTest(CliGuy $I)
    {
        $I->executeCommand('run scenario File.feature --steps');
        $I->seeInShellOutput(<<<EOF
 In order to test a feature
 As a user
 I need to be able to see output
EOF
        );
        $I->seeInShellOutput('Given i have terminal opened');
        $I->seeInShellOutput('When i am in current directory');
        $I->seeInShellOutput('Then there is a file "scenario.suite.yml"');
        $I->seeInShellOutput('And there are keywords in "scenario.suite.yml"');
        $I->seeInShellOutput(<<<EOF
   | class_name | ScenarioGuy |
   | enabled    | Filesystem  |
EOF
        );
        $I->seeInShellOutput('PASSED');
    }

    public function runIncompleteGherkinTest(CliGuy $I)
    {
        $I->executeCommand('run scenario File.feature -v');
        $I->seeInShellOutput('OK, but incomplete');
        $I->seeInShellOutput('Step definition for `I have only idea of what\'s going on here` not found in contexts');
    }


}
