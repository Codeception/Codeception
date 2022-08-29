<?php

declare(strict_types=1);

use Codeception\Attribute\Depends;

#[Depends(ConfigBundledSuitesCest::class . ':runBundledSuite')]
final class ConfigNoActorCest
{
    #[Depends(ConfigBundledSuitesCest::class . ':runBundledSuite')]
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
        $I->executeFailCommand('generate:cest unit Some');
        $I->seeResultCodeIsNot(0);
        $I->executeFailCommand('generate:test unit Some');
        $I->seeResultCodeIs(0);
        $I->seeFileFound('SomeTest.php', 'tests');
        $I->seeInThisFile('class SomeTest extends \Codeception\Test\Unit');
        $I->dontSeeInThisFile('$tester');
        $I->deleteFile('tests/SomeTest.php');
    }
}
