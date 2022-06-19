<?php

declare(strict_types=1);

use Codeception\Attribute\Before;
use Codeception\Attribute\Group;

final class IncludedCest
{
    public function _before()
    {
        $logDir = codecept_root_dir('tests/data/included/_log');
        \Codeception\Util\FileSystem::doEmptyDir($logDir);
    }

    private function moveToIncluded(CliGuy $I)
    {
        $I->amInPath('tests/data/included');
    }

    #[Before('moveToIncluded')]
    public function runSuitesFromIncludedConfigs(CliGuy $I)
    {
        $I->executeCommand('run');
        $I->seeInShellOutput('[Jazz]');
        $I->seeInShellOutput('Functional Tests');
        $I->seeInShellOutput('DemoCept: Check that jazz musicians can add numbers');
        $I->seeInShellOutput('[Jazz\Pianist]');
        $I->dontSeeInShellOutput('Jazz\Pianist.functional Tests');
        $I->seeInShellOutput('PianistCept: Check that jazz pianists can add numbers');
        $I->seeInShellOutput('[Shire]');
        $I->dontSeeInShellOutput('Shire.functional Tests');
        $I->seeInShellOutput('Check that hobbits can add numbers');
    }

    #[Before('moveToIncluded')]
    public function runTestsFromIncludedConfigs(CliGuy $I)
    {
        $ds = DIRECTORY_SEPARATOR;
        $I->executeCommand("run jazz{$ds}tests{$ds}functional{$ds}DemoCept.php", false);

        // Suite is not run
        $I->dontSeeInShellOutput('[Jazz]');

        // DemoCept tests are run
        $I->seeInShellOutput('DemoCept');
        // Other include tests are not run
        $I->dontSeeInShellOutput('[Shire]');
        $I->dontSeeInShellOutput('Shire.functional Tests');
        $I->dontSeeInShellOutput('[Jazz\Pianist]');
        $I->dontSeeInShellOutput('PianistCept: Check that jazz pianists can add numbers');
    }

    #[Before('moveToIncluded')]
    public function runTestsFromIncludedConfigsNested(CliGuy $I)
    {
        $I->executeCommand('run jazz/pianist/tests/functional/PianistCept.php', false);

        // Suite is not run
        $I->dontSeeInShellOutput('[Jazz\Pianist]');

        // DemoCept tests are run
        $I->seeInShellOutput('Functional Tests');
        $I->seeInShellOutput('PianistCept');

        // Other include tests are not run
        $I->dontSeeInShellOutput('[Shire]');
        $I->dontSeeInShellOutput('Check that hobbits can add numbers');
        $I->dontSeeInShellOutput('[Jazz]');
        $I->dontSeeInShellOutput('Jazz.functional Tests');
    }

    #[Before('moveToIncluded')]
    public function runTestsFromIncludedConfigsSingleTest(CliGuy $I)
    {
        $ds = DIRECTORY_SEPARATOR;
        $I->executeCommand("run jazz{$ds}tests{$ds}unit{$ds}SimpleTest.php:testSimple", false);

        // Suite is not run
        $I->dontSeeInShellOutput('[Jazz]');

        // SimpleTest:testSimple is run
        $I->seeInShellOutput('Unit Tests');
        $I->dontSeeInShellOutput('Jazz.functional Tests');
        $I->seeInShellOutput('SimpleTest');

        //  SimpleTest:testSimpler is not run
        $I->dontSeeInShellOutput('SimplerTest');

        // Other include tests are not run
        $I->dontSeeInShellOutput('[Shire]');
        $I->dontSeeInShellOutput('Check that hobbits can add numbers');
        $I->dontSeeInShellOutput('[Jazz\Pianist]');
        $I->dontSeeInShellOutput('PianistCept: Check that jazz pianists can add numbers');
    }

    #[Before('moveToIncluded')]
    #[Group('reports')]
    public function runIncludedWithXmlOutput(CliGuy $I)
    {
        $I->executeCommand('run --xml');
        $I->amInPath('_log');
        $I->seeFileFound('report.xml');
        $I->seeInThisFile('<testsuite name="Jazz.functional" tests="1" assertions="1"');
        $I->seeInThisFile('<testsuite name="Jazz\Pianist.functional" tests="1" assertions="1"');
        $I->seeInThisFile('<testsuite name="Shire.functional" tests="1" assertions="1"');
        $I->seeInThisFile('<testcase name="Hobbit"');
        $I->seeInThisFile('<testcase name="Demo"');
        $I->seeInThisFile('<testcase name="Pianist"');
    }

    #[Before('moveToIncluded')]
    #[Group('reports')]
    public function runIncludedWithHtmlOutput(CliGuy $I)
    {
        $I->executeCommand('run --html');
        $I->amInPath('_log');
        $I->seeFileFound('report.html');
        $I->seeInThisFile('Codeception Results');
        $I->seeInThisFile('Jazz.functional Tests');
        $I->seeInThisFile('Check that jazz musicians can add numbers');
        $I->seeInThisFile('Jazz\Pianist.functional Tests');
        $I->seeInThisFile('Check that jazz pianists can add numbers');
        $I->seeInThisFile('Shire.functional Tests');
    }

    #[Before('moveToIncluded')]
    #[Group('coverage')]
    public function runIncludedWithCoverage(CliGuy $I)
    {
        $I->executeCommand('run --coverage-xml');
        $I->amInPath('_log');
        $I->seeFileFound('coverage.xml');
        //these assertions shrank over the years to be compatible with many versions of php-code-coverage library
        $I->seeInThisFile('BillEvans" namespace="');
        $I->seeInThisFile('Musician" namespace="');
        $I->seeInThisFile('Hobbit" namespace="');
    }

    #[Before('moveToIncluded')]
    public function buildIncluded(CliGuy $I)
    {
        $I->executeCommand('build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInShellOutput('Jazz\\TestGuy');
        $I->seeInShellOutput('Jazz\\Pianist\\TestGuy');
        $I->seeInShellOutput('Shire\\TestGuy');
    }

    #[Before('moveToIncluded')]
    public function cleanIncluded(CliGuy $I)
    {
        $ds = DIRECTORY_SEPARATOR;

        $I->executeCommand('clean');
        $I->seeInShellOutput("included{$ds}_log");
        $I->seeInShellOutput("included{$ds}jazz{$ds}tests/_log");
        $I->seeInShellOutput("included{$ds}jazz{$ds}pianist{$ds}tests/_log");
        $I->seeInShellOutput("included{$ds}shire{$ds}tests/_log");
        $I->seeInShellOutput('Done');
    }

    #[Before('moveToIncluded')]
    public function runIncludedGroup(CliGuy $I)
    {
        $I->executeCommand("run -g group", false);
        $I->dontSeeInShellOutput('No tests executed');
        $I->seeInShellOutput('2 tests');
    }

    public function includedSuitesAreNotRunTwice(CliGuy $I)
    {
        $I->amInPath('tests/data/included_two_config_files');
        $I->executeCommand('run');
        $I->seeInShellOutput('FooTest');
        $I->seeInShellOutput('BarTest');
        $I->seeInShellOutput('2 tests');
        $I->dontSeeInShellOutput('4 tests');
    }

    #[Before('moveToIncluded')]
    public function someSuitesForSomeIncludedApplicationCanBeRun(CliGuy $I)
    {
        $I->executeCommand('run jazz::functional');

        $I->seeInShellOutput('[Jazz]');
        $I->seeInShellOutput('Functional Tests');
        $I->dontSeeInShellOutput('Unit Tests');
        $I->dontSeeInShellOutput('[Shire]');

        $I->executeCommand('run jazz::functional,jazz::unit');

        $I->seeInShellOutput('[Jazz]');
        $I->seeInShellOutput('Functional Tests');
        $I->seeInShellOutput('Unit Tests');
        $I->dontSeeInShellOutput('[Jazz\Pianist]');
        $I->dontSeeInShellOutput('[Shire]');

        $I->executeCommand('run jazz::unit,shire::functional');

        $I->seeInShellOutput('SimpleTest: Simple');
        $I->seeInShellOutput('HobbitCept: Check that hobbits can add numbers');
        $I->dontSeeInShellOutput('DemoCept: Check that jazz musicians can add numbers');

        $I->executeCommand('run jazz/pianist::functional');

        $I->dontSeeInShellOutput('HobbitCept: Check that hobbits can add numbers');
        $I->dontSeeInShellOutput('DemoCept: Check that jazz musicians can add numbers');
        $I->seeInShellOutput('PianistCept: Check that jazz pianists can add numbers');
    }

    #[Before('moveToIncluded')]
    public function someSuitesCanBeRunForAllIncludedApplications(CliGuy $I)
    {
        $I->executeCommand('run *::functional');

        // only functional tests are run
        $I->seeInShellOutput('[Jazz]');
        $I->seeInShellOutput('Functional Tests');
        $I->seeInShellOutput('[Jazz\Pianist]');
        $I->seeInShellOutput('[Shire]');
        $I->dontSeeInShellOutput('Unit Tests');

        $I->executeCommand('run *::unit');
        // only unit tests are run
        $I->seeInShellOutput('[Jazz]');
        $I->seeInShellOutput('Unit Tests');
        $I->dontSeeInShellOutput('Functional Tests');

        $I->executeCommand('run *::functional,*::unit');
        // Both suites are run now
        $I->seeInShellOutput('[Jazz]');
        $I->seeInShellOutput('Functional Tests');
        $I->seeInShellOutput('[Jazz\Pianist]');
        $I->seeInShellOutput('[Shire]');
        $I->seeInShellOutput('Unit Tests');
    }

    #[Before('moveToIncluded')]
    public function wildCardSuitesAndAppSpecificSuitesCantBeCombined(CliGuy $I)
    {
        $I->executeCommand('run jazz::unit,*::functional', false);
        $I->seeResultCodeIs(2);
        $I->seeInShellOutput('Wildcard options can not be combined with specific suites of included apps.');
    }

    #[Before('moveToIncluded')]
    public function runningASuiteInTheRootApplicationDoesNotRunTheIncludedAppSuites(CliGuy $I)
    {
        $I->executeCommand('run unit');

        $I->seeInShellOutput('Unit Tests (1)');
        $I->seeInShellOutput('RootApplicationUnitTest:');

        $I->dontSeeInShellOutput('Functional Tests (1)');
        $I->dontSeeInShellOutput('RootApplicationFunctionalTest:');
        $I->dontSeeInShellOutput('Jazz.functional Tests');
        $I->dontSeeInShellOutput('Jazz.unit Tests');

        $I->executeCommand('run functional');

        $I->seeInShellOutput('Functional Tests (1)');
        $I->seeInShellOutput('RootApplicationFunctionalTest:');

        $I->dontSeeInShellOutput('Unit Tests (1)');
        $I->dontSeeInShellOutput('RootApplicationUnitTest:');
        $I->dontSeeInShellOutput('Jazz.functional Tests');
        $I->dontSeeInShellOutput('Jazz.unit Tests');
    }

    #[Before('moveToIncluded')]
    public function rootSuitesCanBeRunInCombinationWithIncludedSuites(CliGuy $I)
    {
        $I->executeCommand('run unit,*::unit');

        // root level
        $I->seeInShellOutput('Unit Tests (1)');
        $I->seeInShellOutput('RootApplicationUnitTest:');
        $I->dontSeeInShellOutput('Functional Tests (1)');
        $I->dontSeeInShellOutput('RootApplicationFunctionalTest:');

        // included
        $I->seeInShellOutput('[Jazz]');
        $I->seeInShellOutput('Unit Tests');
        $I->dontSeeInShellOutput('Functional Tests');

        // Ensure that root level suites are not run twice.
        $I->seeInShellOutput('OK (3 tests, 3 assertions)');


        $I->executeCommand('run unit,jazz::functional');

        // root level
        $I->seeInShellOutput('Unit Tests (1)');
        $I->seeInShellOutput('RootApplicationUnitTest: Foo equals foo');
        $I->seeInShellOutput('Functional Tests (1)');
        $I->seeInShellOutput('DemoCept: Check that jazz musicians can add number');
        $I->dontSeeInShellOutput('RootApplicationFunctionalTest');
        $I->dontSeeInShellOutput('SimpleTest: Simple');
        $I->dontSeeInShellOutput('HobbitCept: Check that hobbits can add numbers');
        $I->dontSeeInShellOutput('PianistCept: Check that jazz pianists can add numbers');
    }
}
