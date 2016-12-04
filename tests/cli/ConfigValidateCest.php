<?php

class ConfigValidateCest
{
    public function _before(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
    }

    public function printsValidConfig(CliGuy $I)
    {
        $I->executeCommand('config:validate --no-ansi', false);
        $I->dontSeeInShellOutput('ConfigurationException');
        $I->seeInShellOutput('tests => tests');
        $I->seeInShellOutput('data => tests/_data');
    }

    public function validatesInvalidConfigOnParse(CliGuy $I)
    {
        $I->executeCommand('config:validate -c codeception_invalid.yml --no-ansi', false);
        $I->seeInShellOutput('Unable to parse at line 8');
        $I->seeInShellOutput('codeception_invalid.yml');
    }

    public function validatesInvalidConfigBeforeRun(CliGuy $I)
    {
        $I->executeCommand('config:validate -c codeception_invalid.yml --no-ansi', false);
        $I->seeInShellOutput('Unable to parse at line 8');
        $I->seeInShellOutput('codeception_invalid.yml');
    }

    public function validatesConfigWithOverrideOption(CliGuy $I)
    {
        $I->executeCommand('config:validate -o "reporters: report: \Custom\Reporter" --no-ansi');
        $I->seeInShellOutput('report => \Custom\Reporter');
    }

}