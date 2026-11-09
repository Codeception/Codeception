<?php

declare(strict_types=1);

use Tests\Support\CliTester;

final class RunReleaseStepArgumentsCest
{
    public function releasesObjectArgumentsOfFinishedTests(CliTester $I): void
    {
        $I->amInPath('tests/data/release_step_arguments');
        $I->executeCommand('run --no-exit');
        $I->seeInShellOutput('Step arguments holding objects: 0');
    }

    public function keepsStepOutputAfterReleasing(CliTester $I): void
    {
        $I->amInPath('tests/data/release_step_arguments');
        $I->executeCommand('run --no-exit');
        $I->seeInShellOutput('Step  Assert null "ArrayObject"');
        $I->seeInShellOutput('1. $I->assertNull("ArrayObject")');
        $I->seeInShellOutput('Step  Assert null "stringable.txt"');
        $I->seeInShellOutput('1. $I->assertNull("stringable.txt")');
    }

    public function keepsObjectArgumentsWhenDisabled(CliTester $I): void
    {
        $I->amInPath('tests/data/release_step_arguments');
        $I->executeCommand('run --no-exit -o "settings: release_step_arguments: false"');
        $I->seeInShellOutput('Step arguments holding objects: 2');
    }
}
