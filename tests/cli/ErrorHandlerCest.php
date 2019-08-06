<?php
class ErrorHandlerCest
{
    /**
     * @param CliGuy $I
     */
    public function earlyExitWarnsTheUser(\CliGuy $I)
    {
        $I->executeFailCommand('run -c tests/data/first_test_exits');

        $I->seeResultCodeIs(255);
        $I->seeInShellOutput('COMMAND DID NOT FINISH PROPERLY');
    }
}
