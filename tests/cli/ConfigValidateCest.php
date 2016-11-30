<?php

class ConfigValidateCest
{
    public function _before(CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
    }

    public function printsValidConfig(CliGuy $I)
    {
        $I->executeCommand('config:validate', false);
        $I->dontSeeInShellOutput('ConfigurationException');
        $I->seeInShellOutput(
            <<<HERE
    paths => Array
        (
            tests => tests
            log => tests/_output
            data => tests/_data
            helpers => tests/_support
            envs => tests/_envs
        )
HERE
        );
    }

    public function validatesInvalidConfigOnParse(CliGuy $I)
    {
        $I->executeCommand('config:validate -c codeception_invalid.yml', false);
        $I->seeInShellOutput('Unable to parse at line 8');
        $I->seeInShellOutput('codeception_invalid.yml');
    }

    public function validatesInvalidConfigBeforeRun(CliGuy $I)
    {
        $I->executeCommand('config:validate -c codeception_invalid.yml', false);
        $I->seeInShellOutput('Unable to parse at line 8');
        $I->seeInShellOutput('codeception_invalid.yml');
    }

    public function validatesConfigWithOverrideOption(CliGuy $I)
    {
        $I->executeCommand('config:validate -o "reporters: report: \Custom\Reporter"');
        $I->seeInShellOutput('report => \Custom\Reporter');
    }

}