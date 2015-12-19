<?php
class RunEnvironmentCest
{

    public function testDevEnvironment(CliGuy $I)
    {
        $I->wantTo('execute test in --dev environment');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run dummy --env=dev');
        $I->seeInShellOutput("OK (");

    }

    public function testProdEnvironment(CliGuy $I)
    {
        $I->wantTo('execute test in non existent --prod environment');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run dummy --env=prod');
        $I->dontSeeInShellOutput("OK (");
        $I->seeInShellOutput("No tests executed");
    }

    public function testEnvironmentParams(CliGuy $I)
    {
        $I->wantTo('execute check that env params applied');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run powers PowerIsRisingCept.php --env=dev -vv --steps');
        $I->seeInShellOutput('I got the power');
        $I->seeInShellOutput("PASSED");
        $I->seeInShellOutput("OK (");
    }

    public function testWithoutEnvironmentParams(CliGuy $I)
    {
        $I->wantTo('execute check that env params applied');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run powers PowerIsRisingCept.php -vv --no-exit');
        $I->seeInShellOutput("I have no power");
        $I->seeInShellOutput("FAIL");
    }

    public function runTestForSpecificEnvironment(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run powers MageGuildCest.php  --env whisky');
        $I->seeInShellOutput('Red label (MageGuildCest::redLabel)');
        $I->seeInShellOutput('Black label (MageGuildCest::blackLabel)');
        $I->seeInShellOutput('Power of the universe (MageGuildCest::powerOfTheUniverse)');
        $I->seeInShellOutput('OK (3 tests, 3 assertions)');
    }

    public function runTestForNotIncludedEnvironment(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run powers MageGuildCest.php  --env dev');
        $I->seeInShellOutput('Power of the universe (MageGuildCest::powerOfTheUniverse)');
        $I->seeInShellOutput('OK (1 test, 1 assertion)');
    }

    public function testEnvFileLoading(CliGuy $I)
    {
        $I->wantTo('test that env configuration files are loaded correctly');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run messages MessageCest.php:allMessages -vv --env env2');
        $I->seeInShellOutput('message1: MESSAGE1 FROM ENV2-DIST.');
        $I->seeInShellOutput('message2: MESSAGE2 FROM ENV2.');
        $I->seeInShellOutput('message3: MESSAGE3 FROM SUITE.');
        $I->seeInShellOutput('message4: DEFAULT MESSAGE4.');
    }

    public function testEnvMerging(CliGuy $I)
    {
        $I->wantTo('test that given environments are merged properly');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run messages MessageCest.php:allMessages -vv --env env1,env2');
        $I->seeInShellOutput('message1: MESSAGE1 FROM ENV2-DIST.');
        $I->seeInShellOutput('message4: MESSAGE4 FROM SUITE-ENV1.');
        $I->executeCommand('run messages MessageCest.php:allMessages -vv --env env2,env1');
        $I->seeInShellOutput('message1: MESSAGE1 FROM SUITE-ENV1.');
        $I->seeInShellOutput('message4: MESSAGE4 FROM SUITE-ENV1.');
    }

    public function runTestForMultipleEnvironments(CliGuy $I)
    {
        $I->wantTo('check that multiple required environments are taken into account');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run messages MessageCest.php:multipleEnvRequired -vv --env env1');
        $I->dontSeeInShellOutput('Multiple env given');
        $I->executeCommand('run messages MessageCest.php:multipleEnvRequired -vv --env env2');
        $I->dontSeeInShellOutput('Multiple env given');
        $I->executeCommand('run messages MessageCest.php:multipleEnvRequired -vv --env env1,env2');
        $I->seeInShellOutput('Multiple env given');
        $I->executeCommand('run messages MessageCest.php:multipleEnvRequired -vv --env env2,env1');
        $I->seeInShellOutput('Multiple env given');
    }

    public function generateEnvConfig(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('g:env firefox');
        $I->seeInShellOutput('firefox config was created');
        $I->seeFileFound('tests/_envs/firefox.yml');
    }

    public function runEnvironmentForCept(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run messages --env email');
        $I->seeInShellOutput('Test emails');
        $I->dontSeeInShellOutput('Multiple env given');
        $I->executeCommand('run messages --env env1');
        $I->dontSeeInShellOutput('Test emails');
    }

    public function showExceptionForUnconfiguredEnvironment(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run skipped NoEnvironmentCept --no-exit');
        $I->seeInShellOutput("Environment nothing was not configured but used");
        $I->seeInShellOutput('WARNING');
    }

    public function environmentsFromSubfolders(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run messages MessageCest.php:allMessages -vv --env env3');
        $I->seeInShellOutput('MESSAGE2 FROM ENV3');

    }
}
