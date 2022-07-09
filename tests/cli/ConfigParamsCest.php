<?php

declare(strict_types=1);

use Codeception\Attribute\AfterClass;
use Codeception\Attribute\Before;
use Codeception\Attribute\BeforeClass;

final class ConfigParamsCest
{
    #[BeforeClass]
    public function _beforeClass(CliGuy $I)
    {
        $I->amInPath(codecept_root_dir('tests/data/params'));
        $I->executeCommand('build');
        $I->amInPath(codecept_root_dir());
    }

    #[AfterClass]
    public function _afterClass(CliGuy $I)
    {
        $I->amInPath(codecept_root_dir('tests/data/params'));
        $I->executeCommand('clean');
        $I->amInPath(codecept_root_dir());
    }

    private function moveToTestDir(CliGuy $I): void
    {
        $I->amInPath('tests/data/params');
    }

    #[Before('moveToTestDir')]
    public function checkYamlParamsPassed(CliGuy $I)
    {
        $I->executeCommand('run -c codeception_yaml.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    #[Before('moveToTestDir')]
    public function checkDotEnvParamsPassed(CliGuy $I)
    {
        $I->executeCommand('run -c codeception_dotenv.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    #[Before('moveToTestDir')]
    public function checkComplexDotEnvParamsPassed(CliGuy $I)
    {
        $I->executeCommand('run -c codeception_dotenv2.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    #[Before('moveToTestDir')]
    public function checkEnvParamsPassed(CliGuy $I)
    {
        $I->executeCommand('run --no-exit dummy');
        $I->seeInShellOutput('FAILURES');
        $I->seeInShellOutput("Failed asserting that an array contains 'val1'");
    }

    #[Before('moveToTestDir')]
    public function checkParamsPassedInSelf(CliGuy $I)
    {
        $I->executeCommand('run -c codeception_self.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    #[Before('moveToTestDir')]
    public function checkXmlParamsPassed(CliGuy $I)
    {
        $I->executeCommand('run -c codeception_xml.yml dummy');
        $I->seeInShellOutput('OK (1 test');
    }

    #[Before('moveToTestDir')]
    public function checkNonStringParamsAreEncodedProperly(CliGuy $I)
    {
        $I->executeCommand('run -c codeception_yaml.yml complex');
        $I->seeInShellOutput('OK (1 test');
    }
}
