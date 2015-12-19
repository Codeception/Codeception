<?php
class WildcardIncludeCest
{
    /**
     * @after checkAllSuitesExecuted
     * @param CliGuy $I
     */
    public function runIncludedSuites(\CliGuy $I)
    {
        $I->amInPath('tests/data/included_w');
        $I->executeCommand('run');
    }

    /**
     * @after checkAllSuitesExecuted
     * @param \CliGuy $I
     */
    public function runIncludedSuiteFromCurrentDir(\CliGuy $I)
    {
        $I->executeCommand('run -c tests/data/included_w');
    }

    protected function checkAllSuitesExecuted(\CliGuy $I)
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
