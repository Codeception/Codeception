<?php
class IncludedWithGroupsCest
{
    /**
     * @param CliGuy $I
     */
    public function runIncludedSuites(\CliGuy $I)
    {
        $I->amInPath('tests/data/included_with_groups');
        $I->executeCommand('run -g para1');

        $I->seeInShellOutput('SimpleJazzTest: One');
        $I->seeInShellOutput('SimpleShineTest: Two');
        $I->dontSeeInShellOutput('SimpleShineTest: Three');
    }
}
