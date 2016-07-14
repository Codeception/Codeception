<?php

class RegisterCommandCest
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
        $myname = get_current_user();
        $I->amInPath('tests/data/register_command/standard');
        $I->executeCommand('myProject:myCommand');
        $I->seeInShellOutput("Hello {$myname}!");
    }

    public function startMyCommandWithOptionAndConfigurationAtNewPlace(CliGuy $I)
    {
        $myname = get_current_user();
        $I->amInPath('tests/data/register_command');
        $I->executeCommand('myProject:myCommand --config standard/codeception.yml --friendly');
        $I->seeInShellOutput("Hello {$myname},");
        $I->seeInShellOutput("how are you?");
    }
}
