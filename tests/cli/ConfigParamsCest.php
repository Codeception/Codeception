<?php

declare(strict_types=1);

final class ConfigParamsCest
{
    public function checkYamlParamsPassed(CliGuy $I)
    {
        $I->amInPath('tests/data/params');
        $I->executeCommand('run -c codeception_yaml.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    public function checkDotEnvParamsPassed(CliGuy $I)
    {
        $I->amInPath('tests/data/params');
        $I->executeCommand('run -c codeception_dotenv.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    public function checkComplexDotEnvParamsPassed(CliGuy $I)
    {
        $I->amInPath('tests/data/params');
        $I->executeCommand('run -c codeception_dotenv2.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    public function checkEnvParamsPassed(CliGuy $I)
    {
        $I->amInPath('tests/data/params');
        $I->executeCommand('run --no-exit dummy');
        $I->seeInShellOutput('FAILURES');
        $I->seeInShellOutput("Failed asserting that an array contains 'val1'");
    }

    public function checkParamsPassedInSelf(CliGuy $I)
    {
        $I->amInPath('tests/data/params');
        $I->executeCommand('run -c codeception_self.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    public function checkXmlParamsPassed(CliGuy $I)
    {
        $I->amInPath('tests/data/params');
        $I->executeCommand('run -c codeception_xml.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    public function checkNonStringParamsAreEncodedProperly(CliGuy $I)
    {
        $I->amInPath('tests/data/params');
        $I->executeCommand('run -c codeception_yaml.yml complex');
        $I->seeInShellOutput('OK (1 test');
    }
}
