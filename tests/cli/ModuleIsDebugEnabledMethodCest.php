<?php

class ModuleIsDebugEnabledMethodCest
{

    public function _before(\CliGuy $I)
    {
        $I->amInPath('tests/data/sandbox');
    }

    /**
     * @param CliGuy $I
     * @group now
     */
    public function runTestWithoutDebugFlag(CliGuy $I)
    {
        $I->executeCommand('run tests/debug/DebugCest.php:testSomethingWithoutDebugFlag');
        $I->seeInShellOutput("OK (");
    }

    /**
     * @param CliGuy $I
     */
    public function runTestWithDebugFlag(CliGuy $I)
    {
        $I->executeCommand('run tests/debug/DebugCest.php:testSomethingWithDebugFlag --debug');
        $I->seeInShellOutput("OK (");
    }
}