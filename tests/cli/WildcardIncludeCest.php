<?php

declare(strict_types=1);

use Codeception\Attribute\After;

final class WildcardIncludeCest
{
    #[After('checkAllSuitesExecuted')]
    public function runIncludedSuites(CliGuy $I)
    {
        $I->amInPath('tests/data/included_w');
        $I->executeCommand('run');
    }

    #[After('checkAllSuitesExecuted')]
    public function runIncludedSuiteFromCurrentDir(CliGuy $I)
    {
        $I->executeCommand('run -c tests/data/included_w');
    }

    private function checkAllSuitesExecuted(CliGuy $I)
    {
        $I->seeInShellOutput('[ToastPack]');
        $I->seeInShellOutput('ToastPack.unit Tests');
        $I->seeInShellOutput('[EwokPack]');
        $I->seeInShellOutput('EwokPack.unit Tests');
        $I->seeInShellOutput('[AcmePack]');
        $I->seeInShellOutput('AcmePack.unit Tests');
        $I->dontSeeInShellOutput('[Spam]');
        $I->dontSeeInShellOutput('[SpamPack]');
    }
}
