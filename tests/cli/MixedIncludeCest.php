<?php

declare(strict_types=1);

use Codeception\Attribute\After;

final class MixedIncludeCest
{
    #[After('checkAllSuitesExecuted')]
    public function runIncludedSuites(CliGuy $I)
    {
        $I->amInPath('tests/data/included_mix');
        $I->executeCommand('run');
    }

    #[After('checkAllSuitesExecuted')]
    public function runIncludedSuiteFromCurrentDir(CliGuy $I)
    {
        $I->executeCommand('run -c tests/data/included_mix');
    }

    private function checkAllSuitesExecuted(CliGuy $I)
    {
        $I->seeInShellOutput('Unit Tests (1)');
        $I->seeInShellOutput('SimpleTest: Something');
        $I->seeInShellOutput('[AcmePack]: tests from');
        $I->seeInShellOutput('BasicTest: Assert');
    }
}
