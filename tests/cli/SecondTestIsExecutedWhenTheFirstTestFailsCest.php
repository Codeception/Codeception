<?php

declare(strict_types=1);

final class SecondTestIsExecutedWhenTheFirstTestFailsCest
{
    public function testIsExecuted(CliGuy $I)
    {
        $I->wantTo('see that the second test is executed');
        $I->amInPath('tests/data/first_test_fails');
        $I->executeFailCommand('run --xml --no-ansi');
        $I->seeInShellOutput('Tests: 2, Assertions: 1, Errors: 1');
        $I->seeInShellOutput('E TwoTestsCest: Failing');
        $I->seeInShellOutput('+ TwoTestsCest: Successful');
    }

    public function endTestEventIsEmitted(CliGuy $I)
    {
        $I->wantTo('see that all start and end events are emitted');
        $I->amInPath('tests/data/first_test_fails');
        $I->executeFailCommand('run --xml --no-ansi --ext CustomReporter');
        $I->seeInShellOutput('STARTED: TwoTestsCest: Failing');
        $I->seeInShellOutput('ENDED: TwoTestsCest: Failing');
        $I->seeInShellOutput('STARTED: TwoTestsCest: Successful');
        $I->seeInShellOutput('ENDED: TwoTestsCest: Successful');
    }
}
