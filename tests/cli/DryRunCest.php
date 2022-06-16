<?php

class DryRunCest
{
    public function _before(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
    }

    public function runCestWithExamples(CliGuy $I)
    {
        $I->executeCommand('dry-run scenario ExamplesCest --no-ansi');
        $I->seeInShellOutput('ExamplesCest: Files exists annotation');
        $I->seeInShellOutput('I see file found "scenario.suite.yml"');
        $I->seeInShellOutput('I see file found "dummy.suite.yml"');
    }

    public function runFeature(CliGuy $I)
    {
        $I->executeCommand('dry-run scenario File.feature --no-ansi');
        $I->seeInShellOutput('Run gherkin: Check file exists');
        $I->seeInShellOutput('In order to test a feature');
        $I->seeInShellOutput('As a user');
        $I->seeInShellOutput('Given i have terminal opened');
        $I->seeInShellOutput('INCOMPLETE');
        $I->seeInShellOutput('Step definition for `I have only idea of what\'s going on here` not found');
    }

    public function runTestsWithTypedHelper(CliGuy $I)
    {
        if (PHP_VERSION_ID < 80100) {
            $I->markTestSkipped('Requires PHP 8.1');
        }

        $I->amInPath(\codecept_data_dir('typed_helper'));
        $I->executeCommand('build');
        $I->executeCommand('dry-run unit --no-ansi');
        $I->seeInShellOutput('print comment');
        $I->seeInShellOutput('I get int');
        $I->seeInShellOutput('I get dom document');
        $I->seeInShellOutput('I see something');
    }
}
