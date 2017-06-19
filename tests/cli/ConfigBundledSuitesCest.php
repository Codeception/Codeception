<?php

class ConfigBundledSuitesCest
{
    public function runBundledSuite(CliGuy $I)
    {
        $I->amInPath('tests/data/bundled_suites');
        $I->executeCommand('build');
        $I->executeCommand('run -vvv');
        $I->seeInShellOutput('OK (1 test');
    }

    public function generateTestsForBundledSuite(CliGuy $I)
    {
        $I->amInPath('tests/data/bundled_suites');
        $I->executeFailCommand('generate:cept unit Some');
        $I->seeFileFound('SomeCept.php', '.');
        $I->deleteFile('SomeCept.php');
        $I->seeResultCodeIs(0);
        $I->executeFailCommand('generate:cest unit Some');
        $I->seeFileFound('SomeCest.php', '.');
        $I->deleteFile('SomeCest.php');
        $I->seeResultCodeIs(0);
        $I->executeFailCommand('generate:test unit Some');
        $I->seeResultCodeIs(0);
        $I->seeFileFound('SomeTest.php', '.');
        $I->deleteFile('SomeTest.php');
    }
}
