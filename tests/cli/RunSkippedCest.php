<?php

class RunSkippedCest
{
    public function classLevelSkipAnnotationWithMessage(CliGuy $I): void
    {
        $I->amInPath('tests/data/skip');
        $I->executeCommand('run -v --no-ansi unit ClassLevelSkipAnnotationWithMessageCest.php');
        $I->seeInShellOutput("S ClassLevelSkipAnnotationWithMessageCest: Method1");
        $I->seeInShellOutput("S ClassLevelSkipAnnotationWithMessageCest: Method2");
        $I->seeInShellOutput('Skip message');
        $I->seeInShellOutput('OK, but incomplete, skipped, or useless tests!');
    }

    public function classLevelSkipAnnotationWithoutMessage(CliGuy $I): void
    {
        $I->amInPath('tests/data/skip');
        $I->executeCommand('run -v --no-ansi unit ClassLevelSkipAnnotationWithoutMessageCest.php');
        $I->seeInShellOutput("S ClassLevelSkipAnnotationWithoutMessageCest: Method1");
        $I->seeInShellOutput("S ClassLevelSkipAnnotationWithoutMessageCest: Method2");
        $I->seeInShellOutput('OK, but incomplete, skipped, or useless tests!');
    }

    public function classLevelSkipAttributeWithMessage(CliGuy $I): void
    {
        $I->amInPath('tests/data/skip');
        $I->executeCommand('run -v --no-ansi unit ClassLevelSkipAttributeWithMessageCest.php');
        $I->seeInShellOutput("S ClassLevelSkipAttributeWithMessageCest: Method1");
        $I->seeInShellOutput("S ClassLevelSkipAttributeWithMessageCest: Method2");
        $I->seeInShellOutput('Skip message');
        $I->seeInShellOutput('OK, but incomplete, skipped, or useless tests!');
    }

    public function classLevelSkipAttributeWithoutMessage(CliGuy $I): void
    {
        $I->amInPath('tests/data/skip');
        $I->executeCommand('run -v --no-ansi unit ClassLevelSkipAttributeWithoutMessageCest.php');
        $I->seeInShellOutput("S ClassLevelSkipAttributeWithoutMessageCest: Method1");
        $I->seeInShellOutput("S ClassLevelSkipAttributeWithoutMessageCest: Method2");
        $I->seeInShellOutput('OK, but incomplete, skipped, or useless tests!');
    }

    public function methodLevelSkipAnnotationWithMessage(CliGuy $I): void
    {
        $I->amInPath('tests/data/skip');
        $I->executeCommand('run -v --no-ansi unit MethodLevelSkipAnnotationWithMessageCest.php');
        $I->seeInShellOutput("+ MethodLevelSkipAnnotationWithMessageCest: Method1");
        $I->seeInShellOutput("S MethodLevelSkipAnnotationWithMessageCest: Method2");
        $I->seeInShellOutput('Skip message');
        $I->seeInShellOutput('OK, but incomplete, skipped, or useless tests!');
    }

    public function methodLevelSkipAnnotationWithoutMessage(CliGuy $I): void
    {
        $I->amInPath('tests/data/skip');
        $I->executeCommand('run -v --no-ansi unit MethodLevelSkipAnnotationWithoutMessageCest.php');
        $I->seeInShellOutput("+ MethodLevelSkipAnnotationWithoutMessageCest: Method1");
        $I->seeInShellOutput("S MethodLevelSkipAnnotationWithoutMessageCest: Method2");
        $I->seeInShellOutput('OK, but incomplete, skipped, or useless tests!');
    }

    public function methodLevelSkipAttributeWithMessage(CliGuy $I): void
    {
        $I->amInPath('tests/data/skip');
        $I->executeCommand('run -v --no-ansi unit MethodLevelSkipAttributeWithMessageCest.php');
        $I->seeInShellOutput("S MethodLevelSkipAttributeWithMessageCest: Method1");
        $I->seeInShellOutput("+ MethodLevelSkipAttributeWithMessageCest: Method2");
        $I->seeInShellOutput('Skip message');
        $I->seeInShellOutput('OK, but incomplete, skipped, or useless tests!');
    }

    public function methodLevelSkipAttributeWithoutMessage(CliGuy $I): void
    {
        $I->amInPath('tests/data/skip');
        $I->executeCommand('run -v --no-ansi unit MethodLevelSkipAttributeWithoutMessageCest.php');
        $I->seeInShellOutput("+ MethodLevelSkipAttributeWithoutMessageCest: Method1");
        $I->seeInShellOutput("S MethodLevelSkipAttributeWithoutMessageCest: Method2");
        $I->seeInShellOutput('OK, but incomplete, skipped, or useless tests!');
    }
}
