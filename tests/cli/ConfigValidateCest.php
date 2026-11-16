<?php

declare(strict_types=1);

use Tests\Support\CliTester;

final class ConfigValidateCest
{
    public function _before(CliTester $I)
    {
        $I->amInPath('tests/data/sandbox');
    }

    public function printsValidConfig(CliTester $I)
    {
        $I->executeCommand('config:validate --no-ansi', false);
        $I->dontSeeInShellOutput('ConfigurationException');
        $I->seeInShellOutput('tests => tests');
        $I->seeInShellOutput('data => tests/_data');
    }

    public function validatesInvalidConfigOnParse(CliTester $I)
    {
        $I->executeCommand('config:validate -c codeception_invalid.yml --no-ansi', false);
        $I->seeInShellOutput('Unable to parse at line 8');
        $I->seeInShellOutput('codeception_invalid.yml');
    }

    public function validatesInvalidConfigBeforeRun(CliTester $I)
    {
        $I->executeCommand('config:validate -c codeception_invalid.yml --no-ansi', false);
        $I->seeInShellOutput('Unable to parse at line 8');
        $I->seeInShellOutput('codeception_invalid.yml');
    }

    public function validatesConfigWithOverrideOption(CliTester $I)
    {
        $I->executeCommand('config:validate -o "params: foo: bar" --no-ansi');
        $I->seeInShellOutput('foo => bar');
    }

    public function warnsWhenNoSuitesDefined(CliTester $I)
    {
        $I->amInPath(codecept_data_dir('no_suites'));
        $I->executeCommand('config:validate --no-ansi');
        $I->seeInShellOutput('Warning: no suites found');
    }

    public function doesNotWarnForIncludeOnlyConfig(CliTester $I)
    {
        $I->amInPath(codecept_data_dir('included_w'));
        $I->executeCommand('config:validate --no-ansi');
        $I->dontSeeInShellOutput('Warning: no suites found');
    }
}
