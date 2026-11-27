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

    public function validatesSuiteConfigWithOverrideOption(CliTester $I)
    {
        $I->executeCommand('config:validate messages -o "modules: config: MessageHelper: message1: MESSAGE1 FROM OVERRIDE." --no-ansi');
        $I->seeInShellOutput('message1 => MESSAGE1 FROM OVERRIDE.');
        $I->seeInShellOutput('message2 => MESSAGE2 FROM SUITE.');
    }

    public function failsWithOverrideOptionWhenConfigFileIsMissing(CliTester $I)
    {
        $I->amInPath(codecept_data_dir());
        $I->executeCommand('config:validate -o "settings: shuffle: true" --no-ansi 2>&1', false);
        $I->seeInShellOutput('Configuration file could not be found');
        $I->dontSeeInShellOutput('Ok');
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

    public function resolvesPhpConstantInOverrideOption(CliTester $I)
    {
        $I->executeCommand('config:validate -o "params: foo: !php/const PHP_INT_MAX" --no-ansi');
        $I->seeInShellOutput('foo => ' . PHP_INT_MAX);
    }
}
