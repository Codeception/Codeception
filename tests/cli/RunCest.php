<?php

use Codeception\Scenario;

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

    public function runOneFileWithColors(\CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->executeCommand('run --colors tests/dummy/FileExistsCept.php');
        $I->seeInShellOutput("OK (");
        $I->seeInShellOutput("\033[35;1mFileExistsCept:\033[39;22m Check config exists");
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
        $I->assertNotNull(json_decode(file_get_contents('tests/_output/report.json')));
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
    public function runPhpUnitXmlReport(\CliGuy $I)
    {
        $I->wantTo('check phpunit xml reports');
        $I->executeCommand('run dummy --phpunit-xml');
        $I->seeInShellOutput('PHPUNIT-XML report generated in');
        $I->seeFileFound('phpunit-report.xml', 'tests/_output');
        $I->seeInThisFile('<?xml');
        if (\PHPUnit\Runner\Version::series() < 6) {
            $I->seeInThisFile('<testsuite name="dummy" tests="6" assertions="3" failures="0" errors="0" time=');
        } else {
            $I->seeInThisFile('<testsuite name="dummy" tests="6" assertions="3" errors="0" failures="0" skipped="0" time=');
        }
        $I->seeThisFileMatches('/<testsuite name="AnotherCest" file=".*?AnotherCest.php"/');
        $I->seeThisFileMatches('/<testsuite name="AnotherTest" file=".*?AnotherTest.php"/');
        if (\PHPUnit\Runner\Version::series() < 6) {
            $I->seeThisFileMatches('/<testsuite name="AnotherTest" file=".*?AnotherTest.php" tests="2" assertions="2" failures="0" errors="0" time=/');
        } else {
            $I->seeThisFileMatches('/<testsuite name="AnotherTest" file=".*?AnotherTest.php" tests="2" assertions="2" errors="0" failures="0" skipped="0" time=/');
        }
        //FileExistsCept file
        $I->seeInThisFile('<testsuite name="FileExists"');
        $I->seeInThisFile('<testcase name="FileExists"');
        $I->seeInThisFile('feature="');
    }

    /**
     * @group reports
     * @param CliGuy $I
     */
    public function runPhpUnitXmlReportsInStrictMode(\CliGuy $I)
    {
        $I->wantTo('check phpunit xml in strict mode');
        $I->executeCommand('run dummy --phpunit-xml -c codeception_strict_xml.yml');
        $I->seeInShellOutput('PHPUNIT-XML report generated in');
        $I->seeFileFound('phpunit-report.xml', 'tests/_output');
        $I->seeInThisFile('<?xml');
        if (\PHPUnit\Runner\Version::series() < 6) {
            $I->seeInThisFile('<testsuite name="dummy" tests="6" assertions="3" failures="0" errors="0" time=');
        } else {
            $I->seeInThisFile('<testsuite name="dummy" tests="6" assertions="3" errors="0" failures="0" skipped="0" time=');
        }
        $I->seeThisFileMatches('/<testsuite name="AnotherCest" file=".*?AnotherCest.php"/');
        $I->seeThisFileMatches('/<testsuite name="AnotherTest" file=".*?AnotherTest.php"/');
        if (\PHPUnit\Runner\Version::series() < 6) {
            $I->seeThisFileMatches('/<testsuite name="AnotherTest" file=".*?AnotherTest.php" tests="2" assertions="2" failures="0" errors="0" time=/');
        } else {
            $I->seeThisFileMatches('/<testsuite name="AnotherTest" file=".*?AnotherTest.php" tests="2" assertions="2" errors="0" failures="0" skipped="0" time=/');
        }
        //FileExistsCept file
        $I->seeInThisFile('<testsuite name="FileExists"');
        $I->seeInThisFile('<testcase name="FileExists"');
        $I->dontSeeInThisFile('feature="');
    }

    /**
     * @group reports
     *
     * @param CliGuy $I
     */
    public function runCustomReport(\CliGuy $I)
    {
        if (\PHPUnit\Runner\Version::series() >= 7) {
            throw new \PHPUnit\Framework\SkippedTestError('Not for PHPUnit 7');
        }
        $I->executeCommand('run dummy --report -c codeception_custom_report.yml');
        $I->seeInShellOutput('FileExistsCept: Check config exists');
        $I->dontSeeInShellOutput('Ok');
    }

    public function runOneGroup(\CliGuy $I)
    {
        $I->executeCommand('run skipped -g notorun');
        $I->seeInShellOutput('Skipped Tests (1)');
        $I->seeInShellOutput("IncompleteMeCept");
        $I->dontSeeInShellOutput("SkipMeCept");
    }

    public function skipRunOneGroup(\CliGuy $I)
    {
        $I->executeCommand('run skipped --skip-group notorun');
        $I->seeInShellOutput('Skipped Tests (2)');
        $I->seeInShellOutput("SkipMeCept");
        $I->dontSeeInShellOutput("IncompleteMeCept");
    }

    public function skipGroupOfCest(\CliGuy $I)
    {
        $I->executeCommand('run dummy');
        $I->seeInShellOutput('Optimistic');
        $I->seeInShellOutput('Dummy Tests (6)');
        $I->executeCommand('run dummy --skip-group ok');
        $I->seeInShellOutput('Pessimistic');
        $I->seeInShellOutput('Dummy Tests (5)');
        $I->dontSeeInShellOutput('Optimistic');
    }

    public function runTwoSuites(\CliGuy $I)
    {
        $I->executeCommand('run skipped,dummy --no-exit');
        $I->seeInShellOutput("Skipped Tests (3)");
        $I->seeInShellOutput("Dummy Tests (6)");
        $I->dontSeeInShellOutput("Remote Tests");
    }

    public function skipSuites(\CliGuy $I)
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

    public function runOneTestFromUnit(\CliGuy $I)
    {
        $I->executeCommand('run tests/dummy/AnotherTest.php:testFirst');
        $I->seeInShellOutput("AnotherTest: First");
        $I->seeInShellOutput('OK');
        $I->dontSeeInShellOutput('AnotherTest: Second');
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
        $I->seeInShellOutput('Is triangle | "real triangle"');
        $I->seeInShellOutput('Is triangle | #0');
        $I->seeInShellOutput('Is triangle | #1');
        $I->seeInShellOutput('DataProvidersTest');
        $I->seeInShellOutput("OK");
    }

    public function runOneGroupWithDataProviders(\CliGuy $I)
    {
        $I->executeCommand('run unit -g data-providers');
        $I->seeInShellOutput('Is triangle | "real triangle"');
        $I->seeInShellOutput('Is triangle | #0');
        $I->seeInShellOutput('Is triangle | #1');
        $I->seeInShellOutput('DataProvidersTest');
        $I->seeInShellOutput("OK");
    }

    public function runTestWithFailFast(\CliGuy $I)
    {
        $I->executeCommand('run unit --skip-group error --no-exit');
        $I->seeInShellOutput('FailingTest: Me');
        $I->seeInShellOutput("PassingTest: Me");
        $I->executeCommand('run unit --fail-fast --skip-group error --no-exit');
        $I->seeInShellOutput('There was 1 failure');
        $I->dontSeeInShellOutput("PassingTest: Me");
    }

    public function runWithCustomOutputPath(\CliGuy $I)
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

    public function runTestsWithDependencyInjections(\CliGuy $I)
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

    public function runErrorTest(\CliGuy $I)
    {
        $I->executeCommand('run unit ErrorTest --no-exit');
        $I->seeInShellOutput('There was 1 error');
        $I->seeInShellOutput('Array to string conversion');
        $I->seeInShellOutput('ErrorTest.php');
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
        if (!extension_loaded('xdebug')) {
            $scenario->skip("Xdebug not loaded");
        }
        $I->executeCommand('run scenario FailedCept --steps --no-exit');
        $I->seeInShellOutput(<<<EOF
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
        if (!extension_loaded('xdebug')) {
            $scenario->skip("Xdebug not loaded");
        }

        $file = "codeception" . DIRECTORY_SEPARATOR . "c3";
        $I->executeCommand('run scenario SubStepsCept --steps');
        $I->seeInShellOutput(<<<EOF
Scenario --
 I am in path "."
 I see code coverage files are present
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
        if (version_compare(\PHPUnit\Runner\Version::id(), '7.5.5', '<')) {
            $I->seeInShellOutput('Skipped: 2');
        } else {
            //one test fails with Warning instead of Skipped with  PHPUnit >= 7.5.5
            $I->seeInShellOutput('Skipped: 1');
        }
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

    public function reportsCorrectFailedStep(CliGuy $I)
    {
        $I->executeCommand('run scenario File.feature -v');
        $I->seeInShellOutput('OK, but incomplete');
        $I->seeInShellOutput('Step definition for `I have only idea of what\'s going on here` not found in contexts');
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

    protected function checkExampleFiles(CliGuy $I)
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

    public function overrideConfigOptionsToChangeReporter(CliGuy $I)
    {
        if (!class_exists('PHPUnit_Util_Log_TeamCity')) {
            throw new \PHPUnit\Framework\SkippedTestError('Reporter does not exist for this PHPUnit version');
        }
        $I->executeCommand('run scenario --report -o "reporters: report: PHPUnit_Util_Log_TeamCity" --no-exit');
        $I->seeInShellOutput('##teamcity[testStarted');
        $I->dontSeeInShellOutput('............Ok');
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
        $I->seeInShellOutput('OK (15 tests');
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


    public function runWarningTests(CliGuy $I)
    {
        $I->executeCommand('run unit WarningTest.php', false);
        $I->seeInShellOutput('There was 1 warning');
        $I->seeInShellOutput('WarningTest::testWarningInvalidDataProvider');
        $I->seeInShellOutput('Tests: 1,');
        $I->seeInShellOutput('Warnings: 1.');
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
     */
    public function showSameOrderOfFilesOnSeed(CliGuy $I, \Codeception\Scenario $s)
    {
        if (DIRECTORY_SEPARATOR === '\\') {
            $s->skip('Failing on Windows. Need to investigate');
        }
        $I->executeCommand('run unit -o "settings: shuffle: true"', false);
        $I->seeInShellOutput('Seed');
        $output = $I->grabFromOutput('/---\n((.|\n)*?)---/m');
        $output = preg_replace('~\(\d\.\d+s\)~m', '', $output);
        $seed = $I->grabFromOutput('~\[Seed\] (.*)~');

        $I->executeCommand('run unit -o "settings: shuffle: true" --seed ' . $seed, false);
        $newOutput = $I->grabFromOutput('/---\n((.|\n)*?)---/m');
        $newOutput = preg_replace('~\(\d\.\d+s\)~m', '', $newOutput);

        $I->assertEquals($output, $newOutput, 'order of tests is the same');

        $I->executeCommand('run unit -o "settings: shuffle: true"', false);
        $newOutput = $I->grabFromOutput('/---\n((.|\n)*?)---/m');
        $newOutput = preg_replace('~\(\d\.\d+s\)~m', '', $newOutput);

        $I->assertNotEquals($output, $newOutput, 'order of tests is the same');
        }

    public function runCustomBootstrap(\CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->executeCommand('run dummy --bootstrap tests/_init.php');
        $I->seeInShellOutput('--INIT--');
        $I->seeInShellOutput("'hello' => 'world'");
        $I->seeInShellOutput("OK (");
    }

    public function throwErrorIfBootstrapNotFound(\CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->executeCommand('run dummy --bootstrap tests/init.php --no-exit 2>&1', false);
        $I->dontSeeInShellOutput('--INIT--');
        $I->seeInShellOutput("can't be loaded");
        $I->dontSeeInShellOutput("OK (");
    }


    public function runBootstrapInGlobalConfig(\CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->executeCommand('run dummy -c codeception.bootstrap.yml');
        $I->seeInShellOutput('--INIT--');
        $I->seeInShellOutput("'hello' => 'world'");
        $I->seeInShellOutput("OK (");
    }

    public function runBootstrapInSuiteConfig(\CliGuy $I)
    {
        $I->wantTo('execute one test');
        $I->executeCommand('run dummy.bootstrap');
        $I->seeInShellOutput('--INIT--');
        $I->seeInShellOutput("'hello' => 'world'");
        $I->seeInShellOutput("OK (");
    }

    /**
     * @group reports
     *
     * @param CliGuy $I
     */
    public function runHtmlWithPhpBrowserCheckReport(\CliGuy $I)
    {
        $I->wantTo('execute tests with PhpBrowser with html output and check html');
        $I->executeFailCommand('run phpbrowser_html_report --html');
        $I->seeResultCodeIsNot(0);
        $expectedRelReportPath     = 'tests/_output';
        $expectedReportFilename    = 'CodeceptionIssue5568Cest.failureShouldCreateHtmlSnapshot.fail.html';
        $expectedReportAbsFilename = join(DIRECTORY_SEPARATOR, array(
            getcwd(),
            $expectedRelReportPath,
            $expectedReportFilename
        ));
        $I->seeInShellOutput('Html: ' . $expectedReportAbsFilename);
        $I->seeInShellOutput('Response: ' . $expectedReportAbsFilename);
        $I->seeFileFound('report.html', $expectedRelReportPath);
        $I->seeInThisFile("See <a href='" . $expectedReportFilename . "' target='_blank'>HTML snapshot</a> of a failed page");
    }

    protected function htmlReportRegexCheckProvider()
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
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
            ->addStep('no metaStep')
        ],
        [
          'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'commentStepsWithDifferentSubStepsInBetweenAndAfter'))
            ->addStep('no metaStep')
            ->addMetaStep('Page\DemoPageObject: demo action1')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
            ->addStep('no metaStep')
            ->addMetaStep('Page\DemoPageObject: demo action2')
            ->addStep("I don't see file found", 'thisFileAgainDoesNotExist')
        ],
        [
          'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'differentSubSteps'))
            ->addMetaStep('Page\DemoPageObject: demo action1')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
            ->addMetaStep('Page\DemoPageObject: demo action2')
            ->addStep("I don't see file found", 'thisFileAgainDoesNotExist')
        ],
        [
          'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'commentStepsWithDifferentSubStepsOnceNestedInBetweenAndAfter'))
            ->addStep('no metaStep')
            ->addMetaStep('Page\DemoPageObject: demo action1 with nested no metastep')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
            ->addStep('no metaStep inside a method')
            ->addStep('no metaStep')
            ->addMetaStep('Page\DemoPageObject: demo action2')
            ->addStep("I don't see file found", 'thisFileAgainDoesNotExist')
        ],
        [
          'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'commentStepsWithDifferentSubStepsOnceNestedInBetweenAndAfter2'))
            ->addStep('no metaStep')
            ->addMetaStep('Page\DemoPageObject: demo action1 with nested no metastep2')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
            ->addStep('no metaStep inside a private internal method')
            ->addStep('no metaStep')
            ->addMetaStep('Page\DemoPageObject: demo action2')
            ->addStep("I don't see file found", 'thisFileAgainDoesNotExist')
        ],
        [
          'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'nestedSubStepFollowedByOtherSubStep'))
            ->addMetaStep('Page\DemoPageObject: demo action1 with nested no metastep')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
            ->addStep('no metaStep inside a method')
            ->addMetaStep('Page\DemoPageObject: demo action2')
            ->addStep("I don't see file found", 'thisFileAgainDoesNotExist')
        ],
        [
          'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'nestedSubStepFollowedByOtherSubStep2'))
            ->addMetaStep('Page\DemoPageObject: demo action1 with nested no metastep2')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
            ->addStep('no metaStep inside a private internal method')
            ->addMetaStep('Page\DemoPageObject: demo action2')
            ->addStep("I don't see file found", 'thisFileAgainDoesNotExist')
        ],
        [
          'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'twoIdentialSubStepsInARow'))
            ->addMetaStep('Page\DemoPageObject: demo action1')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
            ->addMetaStep('Page\DemoPageObject: demo action1')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
        ],
        [
          'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'twoIdentialSubStepsInARowFollowedByAnotherSubStep'))
            ->addMetaStep('Page\DemoPageObject: demo action1')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
            ->addMetaStep('Page\DemoPageObject: demo action1')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
            ->addMetaStep('Page\DemoPageObject: demo action2')
            ->addStep("I don't see file found", 'thisFileAgainDoesNotExist')
        ],
        [
          'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'twoIdentialSubStepsWithAnotherSubStepInBetween'))
            ->addMetaStep('Page\DemoPageObject: demo action1')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
            ->addMetaStep('Page\DemoPageObject: demo action2')
            ->addStep("I don't see file found", 'thisFileAgainDoesNotExist')
            ->addMetaStep('Page\DemoPageObject: demo action1')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
        ],
        [
          'testHtmlReportRegexBuilder' => (new TestHtmlReportRegexBuilder('CodeceptionIssue4413Cest', 'subStepFollowedByTwoIdentialSubSteps'))
            ->addMetaStep('Page\DemoPageObject: demo action2')
            ->addStep("I don't see file found", 'thisFileAgainDoesNotExist')
            ->addMetaStep('Page\DemoPageObject: demo action1')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
            ->addMetaStep('Page\DemoPageObject: demo action1')
            ->addStep("I don't see file found", 'thisFileDoesNotExist')
            ->addStep("I don't see file found", 'thisFileAlsoDoesNotExist')
        ]
      ];
    }

  /**
   * @group reports
   *
   * @dataProvider htmlReportRegexCheckProvider
   *
   * @param CliGuy               $I
   * @param \Codeception\Example $example
   * @param Scenario             $scenario
   */
  public function runHtmlCheckReport(\CliGuy $I, \Codeception\Example $example, Scenario $scenario)
  {
    if (version_compare(phpversion(), '7.0', '<')) {
      $scenario->skip('This test fails due to another Codeception bug that only happens with PHP 5.6: the execution of single CEST test cases does not work');
    }

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

  /**
   * @return string
   */
  public function build()
  {
    return '/' . $this->regex . '/s';
  }

  /**
   * @param $testBuilder TestHtmlReportRegexBuilder
   * @return HtmlReportRegexBuilder
   */
  public function addTest($testBuilder)
  {
    $this->regex .= $testBuilder->build();
    return $this;
  }

}

class TestHtmlReportRegexBuilder
{

  private $testClass;
  private $testCase;
  private $stepsRegex;

  /**
   * @param $testClass string
   * @param $testCase string
   */
  public function __construct($testClass, $testCase)
  {
    $this->testClass = $testClass;
    $this->testCase = $testCase;
  }

  public function getTestClass()
  {
    return $this->testClass;
  }

  public function getTestCase()
  {
    return $this->testCase;
  }

  /**
   * Allows for nice output in @dataProvider usage.
   *
   * @return string
   */
  public function __toString()
  {
    return $this->getTestClass() . ':' . $this->getTestCase();
  }

  /**
   * @param $step string
   * @param $arg string
   * @return TestHtmlReportRegexBuilder
   */
  public function addStep($step, $arg = null)
  {
    $this->stepsRegex .=  '.*?' . 'stepName ' . '.*?' . $step;
    if ($arg) {
      $this->stepsRegex .= '.*?' . '>&quot;' . $arg . '&quot;';
    }
    return $this;
  }

  public function addMetaStep($step)
  {
    $this->addStep(preg_quote($step));
    $this->stepsRegex .=  '.*?substeps ';
    return $this;
  }


  /**
   * @return string
   */
  public function build()
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
