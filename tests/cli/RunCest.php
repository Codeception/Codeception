<?php

use Codeception\Attribute\DataProvider;
use Codeception\Attribute\Group;
use Codeception\Scenario;

final class RunCest
{
    public function _before(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
    }

    public function runOneFile(CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->executeCommand('run tests/dummy/FileExistsCept.php');
        $I->seeInShellOutput("OK (");
    }

    public function runOneFileWithColors(CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->executeCommand('run --colors tests/dummy/FileExistsCept.php');
        $I->seeInShellOutput("OK (");
        $I->seeInShellOutput("\033[35;1mFileExistsCept:\033[39;22m Check config exists");
    }

    /**
     * https://github.com/Codeception/Codeception/issues/6103
     */
    public function runSuiteWhenNameMatchesExistingDirectory(CliGuy $I)
    {
        $I->amInPath(codecept_data_dir('dir_matches_suite'));
        $I->executeCommand('run api');
        $I->seeInShellOutput('SuccessCest');
    }

    public function runTestsDoesntFail(CliGuy $I)
    {
        $I->amInPath(codecept_data_dir('dir_matches_suite'));
        $I->executeCommand('run tests');
        $I->seeInShellOutput('SuccessCest');
    }

    public function runTestsWithFilterDoesntFail(CliGuy $I)
    {
        $I->amInPath(codecept_data_dir('dir_matches_suite'));
        $I->executeCommand('run tests:^success');
        $I->seeInShellOutput('SuccessCest');

        $I->executeCommand('run tests/:^success');
        $I->seeInShellOutput('SuccessCest');
    }

    public function filterTestsWithoutSpecifyingSuite(CliGuy $I)
    {
        $I->amInPath(codecept_data_dir('dir_matches_suite'));
        $I->executeCommand('run :^success');
        $I->seeInShellOutput('SuccessCest');
    }

    #[Group('reports')]
    public function runHtml(CliGuy $I)
    {
        $I->wantTo('execute tests with html output');
        $I->executeCommand('run dummy --html');
        $I->seeFileFound('report.html', 'tests/_output');
    }


    #[Group('reports')]
    public function runXmlReport(CliGuy $I)
    {
        $I->wantTo('check xml reports');
        $I->executeCommand('run dummy --xml');
        $I->seeFileFound('report.xml', 'tests/_output');
        $I->seeInThisFile('<?xml');
        $I->seeInThisFile('<testsuite name="dummy"');
        $I->seeInThisFile('<testcase name="FileExists"');
        $I->seeInThisFile('feature="');
    }

    #[Group('reports')]
    public function runXmlReportsInStrictMode(CliGuy $I)
    {
        $I->wantTo('check xml in strict mode');
        $I->executeCommand('run dummy --xml -c codeception_strict_xml.yml');
        $I->seeFileFound('report.xml', 'tests/_output');
        $I->seeInThisFile('<?xml');
        $I->seeInThisFile('<testsuite name="dummy"');
        $I->seeInThisFile('<testcase name="FileExists"');
        $I->dontSeeInThisFile('feature="');
    }

    #[Group('reports')]
    public function runPhpUnitXmlReport(CliGuy $I)
    {
        $I->wantTo('check phpunit xml reports');
        $I->executeCommand('run dummy --phpunit-xml');
        $I->seeInShellOutput('PHPUNIT XML report generated in');
        $I->seeFileFound('phpunit-report.xml', 'tests/_output');
        $I->seeInThisFile('<?xml');
        $I->seeInThisFile(
            '<testsuite name="dummy" tests="6" assertions="3" errors="0" failures="0" skipped="0" useless="0"'
            . ' time='
        );
        $I->seeThisFileMatches('/<testsuite name="AnotherCest" file=".*?AnotherCest.php"/');
        $I->seeThisFileMatches('/<testsuite name="AnotherTest" file=".*?AnotherTest.php"/');
        $I->seeThisFileMatches(
            '/<testsuite name="AnotherTest" file=".*?AnotherTest.php" tests="2" assertions="2" errors="0"'
            . ' failures="0" skipped="0" useless="0" time=/'
        );
        //FileExistsCept file
        $I->seeInThisFile('<testsuite name="FileExists"');
        $I->seeInThisFile('<testcase name="FileExists"');
        $I->seeInThisFile('feature="');
    }

    #[Group('reports')]
    public function runPhpUnitXmlReportsInStrictMode(CliGuy $I)
    {
        $I->wantTo('check phpunit xml in strict mode');
        $I->executeCommand('run dummy --phpunit-xml -c codeception_strict_xml.yml');
        $I->seeInShellOutput('PHPUNIT XML report generated in');
        $I->seeFileFound('phpunit-report.xml', 'tests/_output');
        $I->seeInThisFile('<?xml');
        $I->seeInThisFile(
            '<testsuite name="dummy" tests="6" assertions="3" errors="0" failures="0" skipped="0"'
            . ' useless="0" time='
        );
        $I->seeThisFileMatches('/<testsuite name="AnotherCest" file=".*?AnotherCest.php"/');
        $I->seeThisFileMatches('/<testsuite name="AnotherTest" file=".*?AnotherTest.php"/');
        $I->seeThisFileMatches(
            '/<testsuite name="AnotherTest" file=".*?AnotherTest.php" tests="2" assertions="2" errors="0"'
            . ' failures="0" skipped="0" useless="0" time=/'
        );
        //FileExistsCept file
        $I->seeInThisFile('<testsuite name="FileExists"');
        $I->seeInThisFile('<testcase name="FileExists"');
        $I->dontSeeInThisFile('feature="');
    }

    #[Group('reports')]
    public function runCustomReport(CliGuy $I)
    {
        $I->executeCommand('run dummy --ext=MyReportPrinter -c codeception_custom_report.yml');
        $I->seeInShellOutput('FileExistsCept: Check config exists');
        $I->dontSeeInShellOutput('Ok');
    }

    #[Group('reports')]
    public function runCompactReport(CliGuy $I)
    {
        $I->executeCommand('run dummy --report');
        $I->seeInShellOutput('FileExistsCept: Check config exists........................................Ok');
    }

    public function runOneGroup(CliGuy $I)
    {
        $I->executeCommand('run skipped -g notorun');
        $I->seeInShellOutput('Skipped Tests (1)');
        $I->seeInShellOutput("IncompleteMeCept");
        $I->dontSeeInShellOutput("SkipMeCept");
    }

    public function skipRunOneGroup(CliGuy $I)
    {
        $I->executeCommand('run skipped --skip-group notorun');
        $I->seeInShellOutput('Skipped Tests (2)');
        $I->seeInShellOutput("SkipMeCept");
        $I->dontSeeInShellOutput("IncompleteMeCept");
    }

    #[Group('attrs')]
    public function runOneGroupByAttr(CliGuy $I)
    {
        $I->executeCommand('run Attrs -g g1');
        $I->seeInShellOutput("Valid test");
        $I->seeInShellOutput("OK (1 test");
    }

    #[Group('attrs')]
    public function runWithBeforeAfter(CliGuy $I)
    {
        $I->executeCommand('run Attrs --steps -g g1');
        $I->seeInShellOutput("open1");
        $I->seeInShellOutput("open2");
        $I->seeInShellOutput("close1");
        $I->seeInShellOutput("OK (1 test");
    }


    #[Group('attrs')]
    public function runWithExamples(CliGuy $I)
    {
        $I->executeCommand('run Attrs --steps -g e1');
        $I->seeInShellOutput("OK (2 test");
    }


    #[Group('attrs')]
    public function runWithDataprovider(CliGuy $I)
    {
        $I->executeCommand('run Attrs --steps -g d1');
        $I->seeInShellOutput("OK (2 test");
    }

    #[Group('attrs')]
    public function runWithDepends(CliGuy $I)
    {
        $I->executeCommand('run Attrs --steps -g dp');
        $I->seeInShellOutput("This test depends on Attrs\BasicScenarioCest:validTest to pass");
    }

    #[Group('attrs')]
    public function runWithUnitSkipped(CliGuy $I)
    {
        $I->executeCommand('run Attrs --steps -g uskip');
        $I->seeInShellOutput("Skipped: 1");
    }

    #[Group('attrs')]
    public function runWithUnitIncomplete(CliGuy $I)
    {
        $I->executeCommand('run Attrs --steps -g uincomplete');
        $I->seeInShellOutput("Incomplete: 1");
    }

    public function skipGroupOfCest(CliGuy $I)
    {
        $I->executeCommand('run dummy');
        $I->seeInShellOutput('Optimistic');
        $I->seeInShellOutput('Dummy Tests (6)');
        $I->executeCommand('run dummy --skip-group ok');
        $I->seeInShellOutput('Pessimistic');
        $I->seeInShellOutput('Dummy Tests (5)');
        $I->dontSeeInShellOutput('Optimistic');
    }

    public function runTwoSuites(CliGuy $I)
    {
        $I->executeCommand('run skipped,dummy --no-exit');
        $I->seeInShellOutput("Skipped Tests (3)");
        $I->seeInShellOutput("Dummy Tests (6)");
        $I->dontSeeInShellOutput("Remote Tests");
    }

    public function skipSuites(CliGuy $I)
    {
        $I->executeCommand(
            'run dummy --skip skipped --skip remote --skip remote_server --skip order --skip unit '
            . '--skip powers --skip math --skip messages'
        );
        $I->seeInShellOutput("Dummy Tests");
        $I->dontSeeInShellOutput("Remote Tests");
        $I->dontSeeInShellOutput("Remote_server Tests");
        $I->dontSeeInShellOutput("Order Tests");
    }

    public function runOneTestFromUnit(CliGuy $I)
    {
        $I->executeCommand('run tests/dummy/AnotherTest.php:testFirst');
        $I->seeInShellOutput("AnotherTest: First");
        $I->seeInShellOutput('OK');
        $I->dontSeeInShellOutput('AnotherTest: Second');
    }

    public function runOneTestFromCest(CliGuy $I)
    {
        $I->executeCommand('run tests/dummy/AnotherCest.php:optimistic');
        $I->seeInShellOutput("Optimistic");
        $I->dontSeeInShellOutput('Pessimistic');
    }

    public function runTestWithDataProviders(CliGuy $I)
    {
        $I->executeCommand('run tests/unit/DataProvidersTest.php');
        $I->seeInShellOutput('Is triangle | "real triangle"');
        $I->seeInShellOutput('Is triangle | #0');
        $I->seeInShellOutput('Is triangle | #1');
        $I->seeInShellOutput('DataProvidersTest');
        $I->seeInShellOutput("OK");
    }

    public function filterTestsByDataProviderCaseNumber(CliGuy $I)
    {
        $I->executeCommand('run tests/unit/DataProvidersTest.php:#1');
        $I->seeInShellOutput('Is triangle | #1');
        $I->dontSeeInShellOutput('Is triangle | "real triangle"');
        $I->dontSeeInShellOutput('Is triangle | #0');
        $I->seeInShellOutput('DataProvidersTest');
        $I->seeInShellOutput("OK (1 test, 1 assertion)");
    }

    public function filterTestsByDataProviderCaseNumberRange(CliGuy $I)
    {
        $I->executeCommand('run tests/unit/DataProvidersTest.php:#0-1');
        $I->seeInShellOutput('Is triangle | #0');
        $I->seeInShellOutput('Is triangle | #1');
        $I->dontSeeInShellOutput('Is triangle | "real triangle"');
        $I->seeInShellOutput('DataProvidersTest');
        $I->seeInShellOutput("OK (2 tests, 2 assertions)");
    }

    public function filterTestsByDataProviderCaseName(CliGuy $I)
    {
        $I->executeCommand('run tests/unit/DataProvidersTest.php:@"real.*"');
        $I->seeInShellOutput('Is triangle | "real triangle"');
        $I->dontSeeInShellOutput('Is triangle | #0');
        $I->dontSeeInShellOutput('Is triangle | #1');
        $I->seeInShellOutput('DataProvidersTest');
        $I->seeInShellOutput("OK (1 test, 1 assertion)");
    }

    public function filterCestsByDataProviderNumber(CliGuy $I)
    {
        $I->executeCommand('run tests/scenario/DataProviderCest.php:withProtectedDataProvider#1');
        $I->seeInShellOutput('dummy.suite.yml');
        $I->dontSeeInShellOutput('unit.suite.yml');
        $I->dontSeeInShellOutput('summary.suite.yml');
        $I->seeInShellOutput("OK (1 test, 1 assertion)");
    }

    public function filterCestsByExampleNumber(CliGuy $I)
    {
        $I->executeCommand('run tests/scenario/DataProviderCest.php:withDataProviderAndExample#0');
        $I->seeInShellOutput('skipped.suite.yml');
        $I->dontSeeInShellOutput('dummy.suite.yml');
        $I->dontSeeInShellOutput('unit.suite.yml');
        $I->dontSeeInShellOutput('summary.suite.yml');
        $I->seeInShellOutput("OK (1 test, 1 assertion)");
    }

    public function runOneGroupWithDataProviders(CliGuy $I)
    {
        $I->executeCommand('run unit -g data-providers');
        $I->seeInShellOutput('Is triangle | "real triangle"');
        $I->seeInShellOutput('Is triangle | #0');
        $I->seeInShellOutput('Is triangle | #1');
        $I->seeInShellOutput('DataProvidersTest');
        $I->seeInShellOutput("OK");
    }

    public function runTestWithFailFastDefault(CliGuy $I)
    {
        $I->executeCommand('run unit --skip-group error --skip-group multiple-fail --no-exit');
        $I->seeInShellOutput('FailingTest: Me');
        $I->seeInShellOutput("PassingTest: Me");
        $I->executeCommand('run unit --fail-fast --skip-group error --skip-group multiple-fail --no-exit');
        $I->seeInShellOutput('There was 1 failure');
        $I->dontSeeInShellOutput("PassingTest: Me");
    }

    public function runTestWithFailFastCustom(CliGuy $I)
    {
        $I->executeCommand('run unit MultipleFailingTest.php --fail-fast=2 --no-exit');
        $I->seeInShellOutput('There were 2 failures');
        $I->executeCommand('run unit MultipleFailingTest.php --no-exit');
        $I->seeInShellOutput('There were 3 failures');
    }

    #[Group('reports')]
    public function runWithCustomOutputPath(CliGuy $I)
    {
        $I->executeCommand('run dummy --xml myverycustom.xml --html myownhtmlreport.html');
        $I->seeFileFound('myverycustom.xml', 'tests/_output');
        $I->seeInThisFile('<?xml');
        $I->seeInThisFile('<testsuite name="dummy"');
        $I->seeInThisFile('<testcase name="FileExists"');
        $I->seeFileFound('myownhtmlreport.html', 'tests/_output');
        $I->dontSeeFileFound('report.xml', 'tests/_output');
        $I->dontSeeFileFound('report.html', 'tests/_output');
    }

    public function runTestsWithDependencyInjections(CliGuy $I)
    {
        $I->executeCommand('run math');
        $I->seeInShellOutput('MathCest: Test addition');
        $I->seeInShellOutput('MathCest: Test subtraction');
        $I->seeInShellOutput('MathCest: Test square');
        $I->seeInShellOutput('MathTest: All');
        $I->seeInShellOutput('OK (');
        $I->dontSeeInShellOutput('fail');
        $I->dontSeeInShellOutput('error');
    }

    public function runErrorTest(CliGuy $I)
    {
        $I->executeCommand('run unit ErrorTest --no-exit');
        $I->seeInShellOutput('There was 1 error');
        $I->seeInShellOutput('Array to string conversion');
        $I->seeInShellOutput('ErrorTest.php');
    }

    public function runTestWithException(CliGuy $I)
    {
        $I->executeCommand('run unit ExceptionTest --no-exit -v');
        $I->seeInShellOutput('There was 1 error');
        $I->seeInShellOutput('Helllo!');
        $I->expect('Exceptions are not wrapped into ExceptionWrapper');
        $I->dontSeeInShellOutput('PHPUnit_Framework_ExceptionWrapper');
        $I->seeInShellOutput(\RuntimeException::class);
    }

    public function runTestsWithSteps(CliGuy $I)
    {
        $I->executeCommand('run scenario SuccessCept --steps');
        $I->seeInShellOutput(
            <<<EOF
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
    public function runTestWithFailedScenario(CliGuy $I, $scenario)
    {
        if (!extension_loaded('xdebug')) {
            $scenario->skip("Xdebug not loaded");
        }

        $I->executeCommand('run scenario FailedCept --steps --no-exit');
        $I->seeInShellOutput(
            <<<EOF
FailedCept: Fail when file is not found
Signature: FailedCept
Test: tests/scenario/FailedCept.php
Scenario --
 I am in path "."
 I see file found "games.zip"
 FAIL
EOF
        );
        $I->expect('to see scenario trace');
        $I->seeInShellOutput(
            <<<EOF
Scenario Steps:

 2. \$I->seeFileFound("games.zip") at tests/scenario/FailedCept.php:6
 1. \$I->amInPath(".") at tests/scenario/FailedCept.php:5

EOF
        );
    }

    public function runTestWithSubSteps(CliGuy $I, Scenario $scenario)
    {
        if (!extension_loaded('xdebug')) {
            $scenario->skip("Xdebug not loaded");
        }

        $file = "codeception" . DIRECTORY_SEPARATOR . "c3";
        $I->executeCommand('run scenario SubStepsCept --steps');
        $I->seeInShellOutput(
            <<<EOF
Scenario --
 I am in path "."
 I see code coverage files are present
   I see file found "c3.php"
   I see file found "composer.json"
   I see in this file "{$file}"
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
        $I->seeInShellOutput(
            <<<EOF
 In order to test a feature
 As a user
 I need to be able to see output
EOF
        );
        $I->seeInShellOutput('Given i have terminal opened');
        $I->seeInShellOutput('When i am in current directory');
        $I->seeInShellOutput('Then there is a file "scenario.suite.yml"');
        $I->seeInShellOutput('And there are keywords in "scenario.suite.yml"');
        $I->seeInShellOutput(
            <<<EOF
   | actor   | ScenarioGuy |
   | enabled | Filesystem  |
EOF
        );
        $I->seeInShellOutput('PASSED');
    }

    public function reportsCorrectFailedStep(CliGuy $I)
    {
        $I->executeCommand('run scenario File.feature -v');
        $I->seeInShellOutput('OK, but incomplete');
        $I->seeInShellOutput("Step definition for `I have only idea of what's going on here` not found in contexts");
    }

    public function runFailingGherkinTest(CliGuy $I)
    {
        $I->executeCommand('run scenario Fail.feature -v --no-exit');
        $I->seeInShellOutput('Step  I see file "games.zip"');
        $I->seeInShellOutput('Step  I see file "tools.zip"');
    }

    public function runGherkinScenarioWithMultipleStepDefinitions(CliGuy $I)
    {
        $I->executeCommand('run scenario "File.feature:Check file once more" --steps');
        $I->seeInShellOutput('When there is a file "scenario.suite.yml"');
        $I->seeInShellOutput('Then i see file "scenario.suite.yml"');
        $I->dontSeeInShellOutput('Step definition for `I see file "scenario.suite.yml"` not found in contexts');
        $I->seeInShellOutput('PASSED');
    }

    public function runGherkinScenarioOutline(CliGuy $I)
    {
        $I->executeCommand('run scenario FileExamples.feature -v');
        $I->seeInShellOutput('OK (3 tests');
    }

    /**
     * @param CliGuy $I
     * @after checkExampleFiles
     */
    public function runTestWithAnnotationExamples(CliGuy $I)
    {
        $I->executeCommand('run scenario ExamplesCest:filesExistsAnnotation --steps');
    }

    /**
     * @param CliGuy $I
     * @after checkExampleFiles
     */
    public function runTestWithJsonExamples(CliGuy $I)
    {
        $I->executeCommand('run scenario ExamplesCest:filesExistsByJson --steps');
    }

    /**
     * @param CliGuy $I
     * @after checkExampleFiles
     */
    public function runTestWithArrayExamples(CliGuy $I)
    {
        $I->executeCommand('run scenario ExamplesCest:filesExistsByArray --steps');
    }

    private function checkExampleFiles(CliGuy $I)
    {
        $I->seeInShellOutput('OK (3 tests');
        $I->seeInShellOutput('I see file found "scenario.suite.yml"');
        $I->seeInShellOutput('I see file found "dummy.suite.yml"');
        $I->seeInShellOutput('I see file found "unit.suite.yml"');
    }

    public function runTestWithComplexExample(CliGuy $I)
    {
        $I->executeCommand('run scenario ExamplesCest:filesExistsComplexJson --debug');
        $I->seeInShellOutput('Files exists complex json | {"path":"."');
        $I->seeInShellOutput('OK (1 test');
        $I->seeInShellOutput('I see file found "scenario.suite.yml"');
        $I->seeInShellOutput('I see file found "dummy.suite.yml"');
        $I->seeInShellOutput('I see file found "unit.suite.yml"');
    }

    public function reportersConfigurationSectionIsNotSupported(CliGuy $I)
    {
        $I->executeCommand('run scenario --report -o "reporters: report: PHPUnit_Util_Log_TeamCity" --no-exit');
        $I->seeInShellOutput(
            "WARNING: 'reporters' option is not supported! Custom reporters must be reimplemented as extensions."
        );
        $I->seeInShellOutput('............Ok');
        $I->dontSeeInShellOutput('##teamcity[testStarted');
    }

    public function overrideModuleOptions(CliGuy $I)
    {
        $I->executeCommand('run powers PowerIsRisingCept --no-exit');
        $I->seeInShellOutput('FAILURES');
        $I->executeCommand('run powers PowerIsRisingCept -o "modules: config: PowerHelper: has_power: true" --no-exit');
        $I->dontSeeInShellOutput('FAILURES');
    }

    public function runTestWithAnnotationExamplesFromGroupFileTest(CliGuy $I)
    {
        $I->executeCommand('run scenario -g groupFileTest1 --steps');
        $I->seeInShellOutput('OK (3 tests');
    }

    public function testsWithConditionalFails(CliGuy $I)
    {
        $I->executeCommand('run scenario ConditionalCept --no-exit');
        $I->seeInShellOutput('There were 3 failures');
        $I->seeInShellOutput('Fail  File "not-a-file" not found');
        $I->seeInShellOutput('Fail  File "not-a-dir" not found');
        $I->seeInShellOutput('Fail  File "nothing" not found');
    }

    public function runTestWithAnnotationDataprovider(CliGuy $I)
    {
        $I->executeCommand('run scenario -g dataprovider --steps');
        $I->seeInShellOutput('OK (18 tests');
    }

    public function runFailedTestAndCheckOutput(CliGuy $I)
    {
        $I->executeCommand('run scenario FailedCept', false);
        $testPath = implode(DIRECTORY_SEPARATOR, ['tests', 'scenario', 'FailedCept.php']);
        $I->seeInShellOutput('1) FailedCept: Fail when file is not found');
        $I->seeInShellOutput('Test  ' . $testPath);
        $I->seeInShellOutput('Step  See file found "games.zip"');
        $I->seeInShellOutput('Fail  File "games.zip" not found at ""');
    }

    public function runTestWithCustomSetupMethod(CliGuy $I)
    {
        $I->executeCommand('run powers PowerUpCest');
        $I->dontSeeInShellOutput('FAILURES');
    }

    public function runCestWithTwoFailedTest(CliGuy $I)
    {
        $I->executeCommand('run scenario PartialFailedCest', false);
        $I->seeInShellOutput('See file found "testcasetwo.txt"');
        $I->seeInShellOutput('See file found "testcasethree.txt"');
        $I->seeInShellOutput('Tests: 3,');
        $I->seeInShellOutput('Failures: 2.');
    }

    public function runInvalidDataProvider(CliGuy $I)
    {
        $I->executeCommand('run unit InvalidDataProviderTest.php', false);
        $I->seeInShellOutput('There was 1 error');
        $I->seeInShellOutput('[PHPUnit\Framework\Error] The data provider specified for InvalidDataProviderTest::testInvalidDataProvider is invalid');
        $I->seeInShellOutput('Tests: 1,');
        $I->seeInShellOutput('Errors: 1.');
    }

    /**
     * @group shuffle
     * @param CliGuy $I
     */
    public function showSeedNumberOnShuffle(CliGuy $I)
    {
        $I->executeCommand('run unit -o "settings: shuffle: true"', false);
        $I->seeInShellOutput('Seed');
        $I->executeCommand('run unit', false);
        $I->dontSeeInShellOutput('Seed');
    }

    /**
     * @group shuffle
     * @param CliGuy $I
     * @param Scenario $scenario
     */
    public function showSameOrderOfFilesOnSeed(CliGuy $I, Scenario $scenario)
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $scenario->skip('Failing on Windows. Need to investigate');
        }

        $I->executeCommand('run unit -o "settings: shuffle: true"', false);
        $I->seeInShellOutput('Seed');

        $output = $I->grabFromOutput('/---\n((.|\n)*?)---/m');
        $output = preg_replace('#\(\d\.\d+s\)#m', '', $output);

        $seed = $I->grabFromOutput('~\[Seed\] (.*)~');

        $I->executeCommand('run unit -o "settings: shuffle: true" --seed ' . $seed, false);
        $newOutput = $I->grabFromOutput('/---\n((.|\n)*?)---/m');
        $newOutput = preg_replace('#\(\d\.\d+s\)#m', '', $newOutput);

        $I->assertSame($output, $newOutput, 'order of tests is the same');

        $I->executeCommand('run unit -o "settings: shuffle: true"', false);

        $newOutput = $I->grabFromOutput('/---\n((.|\n)*?)---/m');
        $newOutput = preg_replace('#\(\d\.\d+s\)#m', '', $newOutput);

        $I->assertNotSame($output, $newOutput, 'order of tests is the same');
    }

    public function runCustomBootstrap(CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->executeCommand('run dummy --bootstrap tests/_init.php');
        $I->seeInShellOutput('--INIT--');
        $I->seeInShellOutput("'hello' => 'world'");
        $I->seeInShellOutput("OK (");
    }

    public function throwErrorIfBootstrapNotFound(CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->executeCommand('run dummy --bootstrap tests/init.php --no-exit 2>&1', false);
        $I->dontSeeInShellOutput('--INIT--');
        $I->seeInShellOutput("can't be loaded");
        $I->dontSeeInShellOutput("OK (");
    }

    public function runBootstrapInGlobalConfig(CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->executeCommand('run dummy -c codeception.bootstrap.yml');
        $I->seeInShellOutput('--INIT--');
        $I->seeInShellOutput("'hello' => 'world'");
        $I->seeInShellOutput("OK (");
    }

    public function runBootstrapInSuiteConfig(CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->executeCommand('run dummy.bootstrap');
        $I->seeInShellOutput('--INIT--');
        $I->seeInShellOutput("'hello' => 'world'");
        $I->seeInShellOutput("OK (");
    }

    public function runTestsWithGrep(CliGuy $I)
    {
        $I->executeCommand('run dummy --grep Another --no-ansi');
        $I->dontSeeInShellOutput('GroupEventsCest');
        $I->seeInShellOutput('AnotherCest');

        $I->executeCommand('run dummy --grep Optimistic --no-ansi');
        $I->seeInShellOutput('OK (1 test');
    }

    public function runTestsWithFilter(CliGuy $I)
    {
        $I->executeCommand('run dummy --filter Another --no-ansi');
        $I->dontSeeInShellOutput('GroupEventsCest');
        $I->seeInShellOutput('AnotherCest');
    }

    public function runTestsByShards(CliGuy $I)
    {
        $I->executeCommand('run dummy --shard=1/3 --no-ansi');
        $I->seeInShellOutput('OK (2 tests');
        $I->seeInShellOutput('[Shard 1/3');
        preg_match_all('~\+\s(\w+:\s[\w\s]+)~', $I->grabShellOutput(), $matches);
        $tests1 = $matches[1];

        $I->executeCommand('run dummy --shard=2/3');
        $I->seeInShellOutput('OK (2 tests');
        $I->seeInShellOutput('[Shard 2/3');

        preg_match_all('~\+\s(\w+:\s[\w\s]+)~', $I->grabShellOutput(), $matches);
        $tests2 = $matches[1];

        $I->executeCommand('run dummy --shard=3/3');
        $I->seeInShellOutput('OK (2 tests');
        $I->seeInShellOutput('[Shard 3/3');

        preg_match_all('~\+\s(\w+:\s[\w\s]+)~', $I->grabShellOutput(), $matches);
        $tests3 = $matches[1];

        $I->assertEmpty(array_intersect($tests1, $tests2), 'same tests in shards');
        $I->assertEmpty(array_intersect($tests2, $tests3), 'same tests in shards');
        $I->assertEmpty(array_intersect($tests1, $tests3), 'same tests in shards');
    }

    #[Group('reports')]
    public function runHtmlWithPhpBrowserCheckReport(CliGuy $I)
    {
        $I->wantTo('execute tests with PhpBrowser with html output and check html');
        $I->executeFailCommand('run phpbrowser_html_report --html');
        $I->seeResultCodeIsNot(0);

        $expectedRelReportPath     = 'tests/_output';
        $expectedReportFilename    = 'CodeceptionIssue5568Cest.failureShouldCreateHtmlSnapshot.fail.html';
        $expectedReportAbsFilename = implode(DIRECTORY_SEPARATOR, [
            getcwd(),
            $expectedRelReportPath,
            $expectedReportFilename
        ]);
        $I->seeInShellOutput('Html: ' . $expectedReportAbsFilename);
        $I->seeInShellOutput('Response: ' . $expectedReportAbsFilename);
        $I->seeFileFound('report.html', $expectedRelReportPath);
        $I->seeInThisFile("See <a href='" . $expectedReportFilename . "' target='_blank'>HTML snapshot</a> of a failed page");
    }

    private function htmlReportRegexCheckProvider(): array
    {
        return [
            [
                'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'twoCommentStepsInARow'))
                    ->addStep('no metaStep')
                    ->addStep('no metaStep')
            ],
            [
                'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'twoCommentStepsInARowViaPageObjectActor'))
                    ->addStep('no metaStep')
                    ->addStep('no metaStep')
            ],
            [
                'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'twoCommentStepsWithOneSubStepInBetween'))
                    ->addStep('no metaStep')
                    ->addMetaStep('Page\DemoPageObject: demo action1')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
                    ->addStep('no metaStep')
            ],
            [
                'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'commentStepsWithDifferentSubStepsInBetweenAndAfter'))
                    ->addStep('no metaStep')
                    ->addMetaStep('Page\DemoPageObject: demo action1')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
                    ->addStep('no metaStep')
                    ->addMetaStep('Page\DemoPageObject: demo action2')
                    ->addStep("I don&#039;t see file found", 'thisFileAgainDoesNotExist')
            ],
            [
                'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'differentSubSteps'))
                    ->addMetaStep('Page\DemoPageObject: demo action1')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
                    ->addMetaStep('Page\DemoPageObject: demo action2')
                    ->addStep("I don&#039;t see file found", 'thisFileAgainDoesNotExist')
            ],
            [
                'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'commentStepsWithDifferentSubStepsOnceNestedInBetweenAndAfter'))
                    ->addStep('no metaStep')
                    ->addMetaStep('Page\DemoPageObject: demo action1 with nested no metastep')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
                    ->addStep('no metaStep inside a method')
                    ->addStep('no metaStep')
                    ->addMetaStep('Page\DemoPageObject: demo action2')
                    ->addStep("I don&#039;t see file found", 'thisFileAgainDoesNotExist')
            ],
            [
                'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'commentStepsWithDifferentSubStepsOnceNestedInBetweenAndAfter2'))
                    ->addStep('no metaStep')
                    ->addMetaStep('Page\DemoPageObject: demo action1 with nested no metastep2')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
                    ->addStep('no metaStep inside a private internal method')
                    ->addStep('no metaStep')
                    ->addMetaStep('Page\DemoPageObject: demo action2')
                    ->addStep("I don&#039;t see file found", 'thisFileAgainDoesNotExist')
            ],
            [
                'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'nestedSubStepFollowedByOtherSubStep'))
                    ->addMetaStep('Page\DemoPageObject: demo action1 with nested no metastep')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
                    ->addStep('no metaStep inside a method')
                    ->addMetaStep('Page\DemoPageObject: demo action2')
                    ->addStep("I don&#039;t see file found", 'thisFileAgainDoesNotExist')
            ],
            [
                'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'nestedSubStepFollowedByOtherSubStep2'))
                    ->addMetaStep('Page\DemoPageObject: demo action1 with nested no metastep2')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
                    ->addStep('no metaStep inside a private internal method')
                    ->addMetaStep('Page\DemoPageObject: demo action2')
                    ->addStep("I don&#039;t see file found", 'thisFileAgainDoesNotExist')
            ],
            [
                'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'twoIdentialSubStepsInARow'))
                    ->addMetaStep('Page\DemoPageObject: demo action1')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
                    ->addMetaStep('Page\DemoPageObject: demo action1')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
            ],
            [
                'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'twoIdentialSubStepsInARowFollowedByAnotherSubStep'))
                    ->addMetaStep('Page\DemoPageObject: demo action1')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
                    ->addMetaStep('Page\DemoPageObject: demo action1')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
                    ->addMetaStep('Page\DemoPageObject: demo action2')
                    ->addStep("I don&#039;t see file found", 'thisFileAgainDoesNotExist')
            ],
            [
                'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'twoIdentialSubStepsWithAnotherSubStepInBetween'))
                    ->addMetaStep('Page\DemoPageObject: demo action1')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
                    ->addMetaStep('Page\DemoPageObject: demo action2')
                    ->addStep("I don&#039;t see file found", 'thisFileAgainDoesNotExist')
                    ->addMetaStep('Page\DemoPageObject: demo action1')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
            ],
            [
                'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'subStepFollowedByTwoIdentialSubSteps'))
                    ->addMetaStep('Page\DemoPageObject: demo action2')
                    ->addStep("I don&#039;t see file found", 'thisFileAgainDoesNotExist')
                    ->addMetaStep('Page\DemoPageObject: demo action1')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
                    ->addMetaStep('Page\DemoPageObject: demo action1')
                    ->addStep("I don&#039;t see file found", 'thisFileDoesNotExist')
                    ->addStep("I don&#039;t see file found", 'thisFileAlsoDoesNotExist')
            ]
        ];
    }

    #[Group('reports')]
    #[DataProvider('htmlReportRegexCheckProvider')]
    public function runHtmlCheckReport(CliGuy $I, \Codeception\Example $example, Scenario $scenario)
    {
        /** @var TestHtmlReportRegexBuilder $testBuilder */
        $testBuilder = $example['testHtmlReportRegexBuilder'];
        $testClass = $testBuilder->getTestClass();
        $testCase = $testBuilder->getTestCase();

        $test = $testClass . ':' . $testCase;
        $I->wantTo('verify that all steps are rendered correctly in HTML report (' . $test . ')');
        $I->executeCommand('run html_report ' . $test . '$ --html');
        $I->seeFileFound('report.html', 'tests/_output');

        // Check HTML report in sufficient detail:
        $builder = (new HtmlReportRegexBuilder())->addTest($testBuilder);
        $I->seeThisFileMatches($builder->build());
    }
}


// Helper classes for test 'runHtmlCheckReport':

class HtmlReportRegexBuilder
{
    private $regex;

    public function build(): string
    {
        return '/' . $this->regex . '/s';
    }

    public function addTest(TestHtmlReportRegexBuilder $testBuilder): self
    {
        $this->regex .= $testBuilder->build();
        return $this;
    }
}

class TestHtmlReportRegexBuilder
{
    private string $testClass;

    private string $testCase;

    private $stepsRegex;

    public function __construct(string $testClass, string $testCase)
    {
        $this->testClass = $testClass;
        $this->testCase = $testCase;
    }

    public function getTestClass(): string
    {
        return $this->testClass;
    }

    public function getTestCase(): string
    {
        return $this->testCase;
    }

    /**
     * Allows for nice output in @dataProvider usage.
     */
    public function __toString(): string
    {
        return $this->getTestClass() . ':' . $this->getTestCase();
    }

    public function addStep(string $step, ?string $arg = null): self
    {
        $this->stepsRegex .=  '.*?' . 'stepName ' . '.*?' . $step;
        if ($arg) {
            $this->stepsRegex .= '.*?' . '>&quot;' . $arg . '&quot;';
        }

        return $this;
    }

    public function addMetaStep(string $step): self
    {
        $this->addStep(preg_quote($step));
        $this->stepsRegex .=  '.*?substeps ';
        return $this;
    }

    public function build(): string
    {
        $regex = 'scenarioRow .*?' . $this->testClass . ' .*? ' . \Codeception\Test\Descriptor::getTestCaseNameAsString($this->testCase);
        if ($this->stepsRegex) {
            $regex .= ' .*?scenarioRow ' . $this->stepsRegex . '.*?';
        } else {
            $regex .= '.*?';
        }

        return $regex;
    }
}
