<?php

declare(strict_types=1);

final class MixedIncludeCest
{
    /**
     * @after checkAllSuitesExecuted
     * @param CliGuy $I
     */
    public function runIncludedSuites(CliGuy $I)
    {
        $I->amInPath('tests/data/included_mix');
        $I->executeCommand('run');
    }

    /**
     * @after checkAllSuitesExecuted
     * @param CliGuy $I
     */
    public function runIncludedSuiteFromCurrentDir(CliGuy $I)
    {
        $I->executeCommand('run -c tests/data/included_mix');
    }

    private function checkAllSuitesExecuted(CliGuy $I)
    {
        $I->seeInShellOutput('AcmePack.unit Tests (1)');
        $I->seeInShellOutput('Unit Tests (1)');
    }
}
