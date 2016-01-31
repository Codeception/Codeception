<?php

class GlobalCommandOptionCest
{
    public function configOption(CliGuy $I)
    {
        $I->wantTo("start codeception with --config option");
        $I->amInPath('tests/data/register_command/');
        $I->executeCommand('--config standard/codeception.yml');
        $I->seeInShellOutput('myProject:myCommand');
    }

    public function configOptionWithEqualSign(CliGuy $I)
    {
        $I->wantTo("start codeception with --config= option");
        $I->amInPath('tests/data/register_command/');
        $I->executeCommand('--config=standard/codeception.yml');
        $I->seeInShellOutput('myProject:myCommand');
    }

    public function configOptionShortcut(CliGuy $I)
    {
        $I->wantTo("start codeception with shortcut -c option");
        $I->amInPath('tests/data/register_command/');
        $I->executeCommand('-c standard/codeception.yml');
        $I->seeInShellOutput('myProject:myCommand');
    }

    public function configStartWithoutOption(CliGuy $I)
    {
        $I->wantTo("start first time codeception without options");
        $I->amInPath('tests/data/register_command/');
        $I->executeCommand('');
        $I->seeInShellOutput('Available commands:');
    }

    public function configStartWithWrongPath(CliGuy $I)
    {
        $I->wantTo('start codeception with wrong path to a codeception.yml file');
        $I->amInPath('tests/data/register_command/');
        $I->executeFailCommand('-c no/exists/codeception.yml');
        $I->seeInShellOutput('Your configuration file `no/exists/codeception.yml` could not be found.');
    }
}
