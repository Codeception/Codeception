<?php

/**
 * @depends ConfigBundledSuitesCest:runBundledSuite
 */
class ConfigNoActorCest
{
    /**
     * @depends ConfigBundledSuitesCest:runBundledSuite
     * @param CliGuy $I
     */
    public function runSuitesWithoutActor(CliGuy $I)
    {
        $I->amInPath('tests/data/no_actor_suites');
        $I->executeCommand('run -vvv');
        $I->seeInShellOutput('OK (1 test');
    }

    public function suitesWithoutActorDontHaveActorFiles(CliGuy $I)
    {
        $I->amInPath('tests/data/no_actor_suites');
        $I->executeCommand('build');
        $I->dontSeeFileFound('*.php', 'tests/_support');
    }

    public function suitesWithoutActorGenerators(CliGuy $I)
    {
        $I->amInPath('tests/data/no_actor_suites');
        $I->executeFailCommand('generate:cept unit Some');
        $I->seeResultCodeIsNot(0);
        $I->executeFailCommand('generate:cest unit Some');
        $I->seeResultCodeIsNot(0);
        $I->executeFailCommand('generate:test unit Some');
        $I->seeResultCodeIs(0);
        $I->seeFileFound('SomeTest.php', 'tests');
        $I->deleteFile('tests/SomeTest.php');
    }

}
