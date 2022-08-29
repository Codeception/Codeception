<?php

declare(strict_types=1);

final class ErrorHandlerCest
{
    public function earlyExitWarnsTheUser(CliGuy $I)
    {
        $I->executeFailCommand('run -c tests/data/first_test_exits');

        $I->seeResultCodeIs(125);
        $I->seeInShellOutput('COMMAND DID NOT FINISH PROPERLY');
    }
}
