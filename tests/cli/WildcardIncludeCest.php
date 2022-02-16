<?php

declare(strict_types=1);

final class WildcardIncludeCest
{
    /**
     * @after checkAllSuitesExecuted
     * @param CliGuy $I
     */
    public function runIncludedSuites(CliGuy $I)
    {
        $I->amInPath('tests/data/included_w');
        $I->executeCommand('run');
    }

    /**
     * @after checkAllSuitesExecuted
     * @param CliGuy $I
     */
    public function runIncludedSuiteFromCurrentDir(CliGuy $I)
    {
        $I->executeCommand('run -c tests/data/included_w');
    }

    private function checkAllSuitesExecuted(CliGuy $I)
    {
        $I->seeInShellOutput('[ToastPack]');
        $I->seeInShellOutput('Unit Tests (0)');
        $I->seeInShellOutput('[EwokPack]');
        $I->seeInShellOutput('Unit Tests (1)');
        $I->seeInShellOutput('[AcmePack]');
        $I->dontSeeInShellOutput('[Spam]');
        $I->dontSeeInShellOutput('[SpamPack]');
    }
}
