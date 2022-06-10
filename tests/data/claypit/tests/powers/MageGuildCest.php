<?php

use Codeception\Attribute\Env;

class MageGuildCest
{
    #[Env('magic', 'dark')]
    public function darkPower(PowerGuy $I)
    {
        $I->castFireball();
    }

    #[Env('magic', 'white')]
    public function whitePower(PowerGuy $I)
    {
        $I->castFireball();
    }

    #[Env('magic', 'green')]
    public function greenPower(PowerGuy $I)
    {
        $I->castFireball();
    }

    #[Env('whisky', 'red')]
    public function redLabel(PowerGuy $I)
    {
        $I->castFireball();
    }

    #[Env('dark', 'whisky')]
    public function blackLabel(PowerGuy $I)
    {
        $I->castFireball();
    }

    public function powerOfTheUniverse(PowerGuy $I)
    {
        $I->castFireball();
    }
}
