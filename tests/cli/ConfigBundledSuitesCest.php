<?php

class ConfigBundledSuitesCest
{
    public function runSuites(CliGuy $I)
    {
        $I->amInPath('tests/data/bundled_suites');
        $I->executeCommand('build');
        $I->executeCommand('run -vvv');
        $I->seeInShellOutput('OK (1 test');
    }

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

}
