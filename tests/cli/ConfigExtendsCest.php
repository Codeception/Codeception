<?php

class ConfigExtendsCest
{
    /**
     * @param CliGuy $I
     */
    public function runIncludedSuites(\CliGuy $I)
    {
        $I->amInPath('tests/data/config_extends');
        $I->executeCommand('run');

        $I->seeInShellOutput('✔');
        $I->seeInShellOutput('UnitCest');
        $I->dontSeeInShellOutput('Exception');
    }
}
