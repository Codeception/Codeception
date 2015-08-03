<?php
use \CliGuy;

class WildcardIncludeCest
{
    public function _before(CliGuy $I)
    { 
    }

    /**
     * @param CliGuy $I
     */
    protected function moveToIncluded(\CliGuy $I)
    {
        $I->amInPath('tests/data/included_w');
    }
    
    /**
     * @before moveToIncluded
     * @param CliGuy $I
     */
    public function runIncludedSuites(\CliGuy $I)
    {
        $I->executeCommand('run');
        $I->seeInShellOutput('[ToastPack]');
        $I->seeInShellOutput('ToastPack.unit Tests');
        $I->seeInShellOutput('[EwokPack]');
        $I->seeInShellOutput('EwokPack.unit Tests');
        $I->seeInShellOutput('[AcmePack]');
        $I->seeInShellOutput('AcmePack.unit Tests');
        $I->dontSeeInShellOutput('[SpamPack]');
    }
    
}
