<?php

declare(strict_types=1);

final class RegisterCommandCest
{
    public function registerCommand(CliGuy $I)
    {
        $I->amInPath('tests/data/register_command/standard');
        $I->executeCommand('list');
        $I->seeInShellOutput('myProject:myCommand');
    }

    public function registerCommandWithConfigurationAtNewPlace(CliGuy $I)
    {
        $I->amInPath('tests/data/register_command/');
        $I->executeCommand('list -c standard/codeception.yml');
        $I->seeInShellOutput('myProject:yourCommand');
    }

    public function startMyCommand(CliGuy $I)
    {
        $userName = get_current_user();
        $I->amInPath('tests/data/register_command/standard');
        $I->executeCommand('myProject:myCommand');
        $I->seeInShellOutput("Hello {$userName}!");
    }

    public function startMyCommandWithOptionAndConfigurationAtNewPlace(CliGuy $I)
    {
        $userName = get_current_user();
        $I->amInPath('tests/data/register_command');
        $I->executeCommand('myProject:myCommand --config standard/codeception.yml --friendly');
        $I->seeInShellOutput("Hello {$userName},");
        $I->seeInShellOutput("how are you?");
    }
}
