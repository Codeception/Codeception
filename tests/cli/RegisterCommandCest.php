<?php

declare(strict_types=1);

use Tests\Support\CliTester;

final class RegisterCommandCest
{
    public function registerCommand(CliTester $I)
    {
        $I->amInPath('tests/data/register_command/standard');
        $I->executeCommand('list');
        $I->seeInShellOutput('myProject:myCommand');
    }

    public function registerCommandWithConfigurationAtNewPlace(CliTester $I)
    {
        $I->amInPath('tests/data/register_command/');
        $I->executeCommand('list -c standard/codeception.yml');
        $I->seeInShellOutput('myProject:yourCommand');
    }

    public function startMyCommand(CliTester $I)
    {
        $userName = get_current_user();
        $I->amInPath('tests/data/register_command/standard');
        $I->executeCommand('myProject:myCommand');
        $I->seeInShellOutput("Hello {$userName}!");
    }

    public function startMyCommandWithOptionAndConfigurationAtNewPlace(CliTester $I)
    {
        $userName = get_current_user();
        $I->amInPath('tests/data/register_command');
        $I->executeCommand('myProject:myCommand --config standard/codeception.yml --friendly');
        $I->seeInShellOutput("Hello {$userName},");
        $I->seeInShellOutput("how are you?");
    }
}
