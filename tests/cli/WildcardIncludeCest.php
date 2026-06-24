<?php

declare(strict_types=1);

use Tests\Support\CliTester;
use Codeception\Attribute\After;

final class WildcardIncludeCest
{
    #[After('checkAllSuitesExecuted')]
    public function runIncludedSuites(CliTester $I)
    {
        $I->amInPath('tests/data/included_w');
        $I->executeCommand('run');
    }

    #[After('checkAllSuitesExecuted')]
    public function runIncludedSuiteFromCurrentDir(CliTester $I)
    {
        $I->executeCommand('run -c tests/data/included_w');
    }

    private function checkAllSuitesExecuted(CliTester $I)
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
