<?php

declare(strict_types=1);

final class ExtensionsCest
{
    public function useAlternativeFormatter(CliGuy $I)
    {
        $I->wantTo('use alternative formatter delivered through extensions');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/dummy/FileExistsCept.php -c codeception_extended.yml');
        $I->dontSeeInShellOutput("Check config");
        $I->seeInShellOutput('[+] FileExistsCept');
        $I->seeInShellOutput('Modules used: Filesystem, DumbHelper');
    }

    public function loadExtensionByOverride(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/dummy/FileExistsCept.php -o "extensions: enabled: [\Codeception\Extension\SimpleReporter]"');
        $I->dontSeeInShellOutput("Check config");
        $I->seeInShellOutput('[+] FileExistsCept');
    }

    public function dynamicallyEnablingExtensions(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run dummy --ext DotReporter');
        $I->seeShellOutputMatches('#\n\n......\n\nTime: 00:00\.\d+, Memory: \d+\.\d+ MB\n\nOK \(6 tests, 3 assertions\)#m');
        $I->dontSeeInShellOutput('Optimistic');
        $I->dontSeeInShellOutput('AnotherCest');
    }

    public function reRunFailedTests(CliGuy $I)
    {
        $ds = DIRECTORY_SEPARATOR;
        $I->amInPath('tests/data/sandbox');

        $I->executeCommand('run unit FailingTest.php -c codeception_extended.yml --no-exit');
        $I->seeInShellOutput('[-] FailingTest:testMe');
        $I->seeFileFound('failed', 'tests/_output');
        $I->seeFileContentsEqual("tests{$ds}unit{$ds}FailingTest.php:testMe");
        $I->executeCommand('run -g failed -c codeception_extended.yml --no-exit');
        $I->seeInShellOutput('[-] FailingTest:testMe');

        $failGroup = "some-failed";
        $I->executeCommand("run unit FailingTest.php -c codeception_extended.yml --no-exit --override \"extensions: config: Codeception\\Extension\\RunFailed: fail-group: {$failGroup}\"");
        $I->seeInShellOutput('[-] FailingTest:testMe');
        $I->seeFileFound($failGroup, 'tests/_output');
        $I->seeFileContentsEqual("tests{$ds}unit{$ds}FailingTest.php:testMe");
        $I->executeCommand("run -g {$failGroup} -c codeception_extended.yml --no-exit --override \"extensions: config: Codeception\\Extension\\RunFailed: fail-group: {$failGroup}\"");
        $I->seeInShellOutput('[-] FailingTest:testMe');
    }

    public function checkIfExtensionsReceiveCorrectOptions(CliGuy $I)
    {
        $I->wantTo('check if extensions receive correct options');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run tests/dummy/AnotherCest.php:optimistic -c codeception_extended.yml');
        $I->seeInShellOutput('Low verbosity');
        $I->executeCommand('run tests/dummy/AnotherCest.php:optimistic -c codeception_extended.yml -v');
        $I->seeInShellOutput('Medium verbosity');
        $I->executeCommand('run tests/dummy/AnotherCest.php:optimistic -c codeception_extended.yml -vv');
        $I->seeInShellOutput('High verbosity');
        $I->executeCommand('run tests/dummy/AnotherCest.php:optimistic -c codeception_extended.yml -vvv');
        $I->seeInShellOutput('Extreme verbosity');
    }

    public function runPerSuiteExtensions(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run extended,scenario', false);
        $I->seeInShellOutput('Suite setup for extended');
        $I->seeInShellOutput('Test setup for Hello');
        $I->seeInShellOutput('Test teardown for Hello');
        $I->seeInShellOutput('Suite teardown for extended');
        $I->dontSeeInShellOutput('Suite setup for scenario');
        $I->seeInShellOutput('Config1: value1');
        $I->seeInShellOutput('Config2: value2');
    }

    public function runPerSuiteExtensionsInEnvironment(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run extended --env black', false);
        $I->seeInShellOutput('Suite setup for extended');
        $I->seeInShellOutput('Test setup for Hello');
        $I->seeInShellOutput('Config1: black_value');
        $I->seeInShellOutput('Config2: value2');
    }

    public function runtimeExtensionsWorkWithIncludedSuitesPresentInTheConfigAndRunningARootSuite(CliGuy $I)
    {
        $I->amInPath('tests/data/included_mix');
        // unit is a root suite.
        $I->executeCommand('run unit --ext DotReporter');
        $I->seeInShellOutput('.');
        $I->dontSeeInShellOutput('SimpleTest:');
    }

    public function runtimeExtensionsWorkWhenRunningWildCardSuites(CliGuy $I)
    {
        $I->amInPath('tests/data/included_mix');
        $I->executeCommand('run *::unit --ext DotReporter');
        $I->seeInShellOutput('.');
        $I->dontSeeInShellOutput('BasicTest:');
    }

    public function runtimeExtensionsWorkWhenRunningWildCardSuitesAndRoot(CliGuy $I)
    {
        $I->amInPath('tests/data/included_mix');
        $I->executeCommand('run unit,*::unit --ext DotReporter');
        $I->seeInShellOutput('.');
        $I->dontSeeInShellOutput('SimpleTest:');
        $I->dontSeeInShellOutput('BasicTest:');
    }

    public function runtimeExtensionsWorkWhenRunningTestsFromAnIncludedConfig(CliGuy $I)
    {
        $I->amInPath('tests/data/included');

        $ds = DIRECTORY_SEPARATOR;
        $I->executeCommand("run jazz{$ds}tests{$ds}functional{$ds}DemoCept.php --ext DotReporter", false);
        $I->seeInShellOutput('.');
        $I->dontSeeInShellOutput('DemoCept:');
    }
}
