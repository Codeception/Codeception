<?php

class RetryCest
{
    public function _before(CliGuy $I)
    {
        $I->amInPath('tests/data/retries');
    }

    public function checkTheTestsPassesOn3rdRetry(CliGuy $I)
    {
        $I->executeCommand('run --debug -g pass1');
        $I->seeInShellOutput('Retrying #3');
        $I->seeResultCodeIs(0);
    }

    public function checkThatRetryHappensOnSpecificInterval(CliGuy $I)
    {
        $I->executeCommand('run --debug -g pass2');
        $I->seeInShellOutput('Retrying #1 in 200ms');
        $I->seeInShellOutput('Retrying #2 in 400ms');
    }

    public function checkTheTestFailsEvenAfterRetries(CliGuy $I)
    {
        $I->executeFailCommand('run --debug -g fail1');
        $I->seeResultCodeIsNot(0);
        $I->seeInShellOutput('Retrying #2');
    }


    public function checkTheTestFailsEvenWhenRetriesByInterval(CliGuy $I)
    {
        $I->executeFailCommand('run --debug -g fail2');
        $I->seeInShellOutput('Retrying #1 in 100ms');
        $I->seeInShellOutput('Retrying #2 in 200ms');
        $I->seeInShellOutput('Retrying #3 in 400ms');
        $I->seeResultCodeIsNot(0);
    }

    public function checkThatTryToWontFailTest(CliGuy $I)
    {
        $I->executeCommand('run --debug -g ignore');
        $I->seeInShellOutput('Failed to perform');
        $I->seeResultCodeIs(0);
    }

}