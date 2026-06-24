<?php

declare(strict_types=1);

use Tests\Support\CliTester;
use Codeception\Attribute\Before;

final class ConfigParamsCest
{
    private function moveToTestDir(CliTester $I): void
    {
        static $prepared = false;

        $I->amInPath('tests/data/params');

        if ($prepared) {
            return;
        }
        $I->executeCommand('build');
        $prepared = true;
    }

    #[Before('moveToTestDir')]
    public function checkYamlParamsPassed(CliTester $I)
    {
        $I->executeCommand('run -c codeception_yaml.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    #[Before('moveToTestDir')]
    public function checkDotEnvParamsPassed(CliTester $I)
    {
        $I->executeCommand('run -c codeception_dotenv.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    #[Before('moveToTestDir')]
    public function checkComplexDotEnvParamsPassed(CliTester $I)
    {
        $I->executeCommand('run -c codeception_dotenv2.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    #[Before('moveToTestDir')]
    public function checkEnvParamsPassed(CliTester $I)
    {
        $I->executeCommand('run --no-exit dummy');
        $I->seeInShellOutput('FAILURES');
        $I->seeInShellOutput("Failed asserting that an array contains 'val1'");
    }

    #[Before('moveToTestDir')]
    public function checkParamsPassedInSelf(CliTester $I)
    {
        $I->executeCommand('run -c codeception_self.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    #[Before('moveToTestDir')]
    public function checkXmlParamsPassed(CliTester $I)
    {
        $I->executeCommand('run -c codeception_xml.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    #[Before('moveToTestDir')]
    public function checkNonStringParamsAreEncodedProperly(CliTester $I)
    {
        $I->executeCommand('run -c codeception_yaml.yml complex');
        $I->seeInShellOutput('OK (1 test');
    }
}
