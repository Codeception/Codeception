<?php

class SecondTestIsExecutedWhenTheFirstTestFailsCest
{
    public function testIsExecuted(CliGuy $I)
    {
        $I->wantTo('see that the second test is executed');
        $I->amInPath('tests/data/first_test_fails');
        $I->executeFailCommand('run --xml --no-ansi');
        $I->seeInShellOutput('Tests: 2, Assertions: 1, Errors: 1');
        $I->seeInShellOutput('E twoTestsCest: Failing');
        $I->seeInShellOutput('+ twoTestsCest: Successful');
    }

    public function endTestEventIsEmitted(CliGuy $I)
    {
        $I->wantTo('see that all start and end events are emitted');
        $I->amInPath('tests/data/first_test_fails');
        $I->executeFailCommand('run --xml --no-ansi --report -o "reporters: report: CustomReporter"');
        $I->seeInShellOutput('STARTED: twoTestsCest: Failing');
        $I->seeInShellOutput('ENDED: twoTestsCest: Failing');
        $I->seeInShellOutput('STARTED: twoTestsCest: Successful');
        $I->seeInShellOutput('ENDED: twoTestsCest: Successful');
    }
}
