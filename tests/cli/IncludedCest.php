<?php
class IncludedCest
{

    public function _before()
    {
        $logDir = codecept_root_dir('tests/data/included/_log');
        \Codeception\Util\FileSystem::doEmptyDir($logDir);
    }

    /**
     * @param CliGuy $I
     */
    protected function moveToIncluded(\CliGuy $I)
    {
        $I->amInPath('tests/data/included');
    }

    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function runSuitesFromIncludedConfigs(\CliGuy $I)
    {
        $I->executeCommand('run');
        $I->seeInShellOutput('[Jazz]');
        $I->seeInShellOutput('Jazz.functional Tests');
        $I->seeInShellOutput('[Jazz\Pianist]');
        $I->seeInShellOutput('Jazz\Pianist.functional Tests');
        $I->seeInShellOutput('[Shire]');
        $I->seeInShellOutput('Shire.functional Tests');
    }

    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function runTestsFromIncludedConfigs(\CliGuy $I)
    {
        $ds = DIRECTORY_SEPARATOR;
        $I->executeCommand("run jazz{$ds}tests{$ds}functional{$ds}DemoCept.php", false);

        // Suite is not run
        $I->dontSeeInShellOutput('[Jazz]');

        // DemoCept tests are run
        $I->seeInShellOutput('Jazz.functional Tests');
        $I->seeInShellOutput('DemoCept');

        // Other include tests are not run
        $I->dontSeeInShellOutput('[Shire]');
        $I->dontSeeInShellOutput('Shire.functional Tests');
        $I->dontSeeInShellOutput('[Jazz\Pianist]');
        $I->dontSeeInShellOutput('Jazz\Pianist.functional Tests');
    }

    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function runTestsFromIncludedConfigsNested(\CliGuy $I)
    {
        $I->executeCommand('run jazz/pianist/tests/functional/PianistCept.php', false);

        // Suite is not run
        $I->dontSeeInShellOutput('[Jazz\Pianist]');

        // DemoCept tests are run
        $I->seeInShellOutput('Jazz\Pianist.functional Tests');
        $I->seeInShellOutput('PianistCept');

        // Other include tests are not run
        $I->dontSeeInShellOutput('[Shire]');
        $I->dontSeeInShellOutput('Shire.functional Tests');
        $I->dontSeeInShellOutput('[Jazz]');
        $I->dontSeeInShellOutput('Jazz.functional Tests');
    }

    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function runTestsFromIncludedConfigsSingleTest(\CliGuy $I)
    {
        $ds = DIRECTORY_SEPARATOR;
        $I->executeCommand("run jazz{$ds}tests{$ds}unit{$ds}SimpleTest.php:testSimple", false);

        // Suite is not run
        $I->dontSeeInShellOutput('[Jazz]');

        // SimpleTest:testSimple is run
        $I->seeInShellOutput('Jazz.unit Tests');
        $I->dontSeeInShellOutput('Jazz.functional Tests');
        $I->seeInShellOutput('SimpleTest');

        //  SimpleTest:testSimpler is not run
        $I->dontSeeInShellOutput('SimplerTest');

        // Other include tests are not run
        $I->dontSeeInShellOutput('[Shire]');
        $I->dontSeeInShellOutput('Shire.functional Tests');
        $I->dontSeeInShellOutput('[Jazz\Pianist]');
        $I->dontSeeInShellOutput('Jazz\Pianist.functional Tests');
    }

    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function runIncludedWithXmlOutput(\CliGuy $I)
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

    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function runIncludedWithHtmlOutput(\CliGuy $I)
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

    /**
     * @before moveToIncluded
     * @group coverage
     * @param CliGuy $I
     */
    public function runIncludedWithCoverage(\CliGuy $I)
    {
        $I->executeCommand('run --coverage-xml');
        $I->amInPath('_log');
        $I->seeFileFound('coverage.xml');
        //these assertions shrank over the years to be compatible with many versions of php-code-coverage library
        $I->seeInThisFile('BillEvans" namespace="');
        $I->seeInThisFile('Musician" namespace="');
        $I->seeInThisFile('Hobbit" namespace="');
    }

    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function buildIncluded(\CliGuy $I)
    {
        $I->executeCommand('build');
        $I->seeInShellOutput('generated successfully');
        $I->seeInShellOutput('Jazz\\TestGuy');
        $I->seeInShellOutput('Jazz\\Pianist\\TestGuy');
        $I->seeInShellOutput('Shire\\TestGuy');
    }

	/**
      * @before moveToIncluded
      * @param CliGuy $I
      */
    public function cleanIncluded(\CliGuy $I)
    {
        $ds = DIRECTORY_SEPARATOR;

        $I->executeCommand('clean');
        $I->seeInShellOutput("included{$ds}_log");
        $I->seeInShellOutput("included{$ds}jazz{$ds}tests/_log");
        $I->seeInShellOutput("included{$ds}jazz{$ds}pianist{$ds}tests/_log");
        $I->seeInShellOutput("included{$ds}shire{$ds}tests/_log");
        $I->seeInShellOutput('Done');
    }

    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function runIncludedGroup(\CliGuy $I)
    {
        $I->executeCommand("run -g group", false);
        $I->dontSeeInShellOutput('No tests executed');
        $I->seeInShellOutput('2 tests');
    }

    /**
     * @param CliGuy $I
     */
    public function includedSuitesAreNotRunTwice (CliGuy $I) {

        $I->amInPath('tests/data/included_two_config_files');
        $I->executeCommand('run');
        $I->seeInShellOutput('FooTest');
        $I->seeInShellOutput('BarTest');
        $I->seeInShellOutput('2 tests');
        $I->dontSeeInShellOutput('4 tests');
    }
    
    /**
     * @param CliGuy $I
     * @before moveToIncluded
     */
    public function overwrittenConfigurationIsAlsoAppliedWhenRunningAnIncludedAppFromTheRootApp(CliGuy $I)
    {
        if (version_compare(\PHPUnit\Runner\Version::id(), '7.0.0', '<')) {
            $I->markTestSkipped('This test can only run on PHP >= 7.1 because of reporter type declarations.');
        }
        $I->executeCommand('run jazz/tests/functional --report -o "reporters: report: Jazz\CustomReporter1"');
        $I->seeInShellOutput('CustomReporter1');
    }
    
    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function someSuitesForSomeIncludedApplicationCanBeRun(CliGuy $I)
    {
        $I->executeCommand('run jazz::functional');
    
        $I->seeInShellOutput('Jazz.functional Tests');
        $I->dontSeeInShellOutput('Jazz.unit Tests');
        $I->dontSeeInShellOutput('Shire.functional');
    
        $I->executeCommand('run jazz::functional,jazz::unit');
        
        $I->seeInShellOutput('Jazz.functional Tests');
        $I->seeInShellOutput('Jazz.unit Tests');
        
        $I->dontSeeInShellOutput('Shire.functional');
    
        $I->executeCommand('run jazz::unit,shire::functional');
        
        $I->seeInShellOutput('Shire.functional Tests');
        $I->seeInShellOutput('Jazz.unit Tests');
        $I->dontSeeInShellOutput('Jazz.functional Tests');
    
        $I->executeCommand('run jazz/pianist::functional');
    
        $I->dontSeeInShellOutput('Jazz.functional Tests');
        $I->seeInShellOutput('Jazz\Pianist.functional');
    }
    
    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function someSuitesCanBeRunForAllIncludedApplications(\CliGuy $I)
    {
        $I->executeCommand('run *::functional');
    
        // only functional tests are run
        $I->seeInShellOutput('Jazz.functional Tests');
        $I->seeInShellOutput('Jazz\Pianist.functional');
        $I->seeInShellOutput('Shire.functional Tests');
        // unit suites are not run
        $I->dontSeeInShellOutput('Jazz.unit Tests');
        
        
        $I->executeCommand('run *::unit');
        // only unit tests are run
        $I->seeInShellOutput('Jazz.unit Tests');
        $I->dontSeeInShellOutput('Jazz.functional Tests');
        $I->dontSeeInShellOutput('Jazz\Pianist.functional');
        $I->dontSeeInShellOutput('Shire.functional Tests');
       
        $I->executeCommand('run *::functional,*::unit');
        // Both suites are run now
        $I->seeInShellOutput('Jazz.functional Tests');
        $I->seeInShellOutput('Jazz\Pianist.functional');
        $I->seeInShellOutput('Shire.functional Tests');
        $I->seeInShellOutput('Jazz.unit Tests');
    }
    
    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function wildCardSuitesAndAppSpecificSuitesCantBeCombined(CliGuy $I)
    {
        $I->executeCommand('run jazz::unit,*::functional', false);
        $I->seeResultCodeIs(2);
        $I->seeInShellOutput('Wildcard options can not be combined with specific suites of included apps.');
    }
    
    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
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
    
    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function rootSuitesCanBeRunInCombinationWithIncludedSuites(CliGuy $I)
    {
        $I->executeCommand('run unit,*::unit');
    
        // root level
        $I->seeInShellOutput('Unit Tests (1)');
        $I->seeInShellOutput('RootApplicationUnitTest:');
        $I->dontSeeInShellOutput('Functional Tests (1)');
        $I->dontSeeInShellOutput('RootApplicationFunctionalTest:');
        
        // included
        $I->seeInShellOutput('Jazz.unit Tests');
        $I->dontSeeInShellOutput('Jazz.functional Tests');
        $I->dontSeeInShellOutput('Jazz\Pianist.functional');
        $I->dontSeeInShellOutput('Shire.functional Tests');
        
        // Ensure that root level suites are not run twice.
        $I->seeInShellOutput('OK (3 tests, 3 assertions)');
    
    
        $I->executeCommand('run unit,jazz::functional');
    
        // root level
        $I->seeInShellOutput('Unit Tests (1)');
        $I->seeInShellOutput('RootApplicationUnitTest:');
        $I->dontSeeInShellOutput('Functional Tests (1)');
        $I->dontSeeInShellOutput('RootApplicationFunctionalTest:');
    
        // included apps
        $I->seeInShellOutput('Jazz.functional Tests');
        $I->dontSeeInShellOutput('Jazz.unit Tests');
        $I->dontSeeInShellOutput('Jazz\Pianist.functional');
        $I->dontSeeInShellOutput('Shire.functional Tests');
        
    }
    
}
