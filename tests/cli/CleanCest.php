<?php

class CleanCest
{
    public function cleanDoesNotDeleteGitKeepFiles(\CliGuy $I)
    {
        $ds = DIRECTORY_SEPARATOR;

        $I->amInPath('tests/data/included');
        $I->executeCommand('clean');
        $I->seeInShellOutput("included{$ds}_log");
        $I->seeInShellOutput("included{$ds}jazz{$ds}tests/_log");
        $I->seeInShellOutput("included{$ds}jazz{$ds}pianist{$ds}tests/_log");
        $I->seeInShellOutput("included{$ds}shire{$ds}tests/_log");
        $I->seeInShellOutput('Done');
        $I->seeFileFound("_log/.gitkeep");
        $I->seeFileFound("jazz{$ds}tests/_log/.gitkeep");
        $I->seeFileFound("jazz{$ds}pianist{$ds}tests/_log/.gitkeep");
        $I->seeFileFound("shire{$ds}tests/_log/.gitkeep");
    }
}
