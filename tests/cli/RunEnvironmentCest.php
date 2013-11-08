<?php
use \CliGuy;

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
        $I->executeCommand('run powers --env=dev -vv');
        $I->seeInShellOutput('I got the power');
        $I->seeInShellOutput("PASSED");
        $I->seeInShellOutput("OK (");
    }

    public function testWithoutEnvironmentParams(CliGuy $I)
    {
        $I->wantTo('execute check that env params applied');
        $I->amInPath('tests/data/sandbox');
        $I->executeCommand('run powers -vv --no-exit');
        $I->seeInShellOutput("I have no power");
        $I->seeInShellOutput("FAIL");
    }


}