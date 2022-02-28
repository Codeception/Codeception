<?php

class MageGuildCest
{
    /**
     * @env magic
     * @env dark
     */
    public function darkPower(PowerGuy $I)
    {
        $I->castFireball();
    }

    /**
     * @env magic
     * @env white
     */
    public function whitePower(PowerGuy $I)
    {
        $I->castFireball();
    }

    /**
     * @env magic
     * @env green
     */
    public function greenPower(PowerGuy $I)
    {
        $I->castFireball();
    }

    /**
     * @env whisky
     * @env red
     */
    public function redLabel(PowerGuy $I)
    {
        $I->castFireball();
    }

    /**
     * @env dark
     * @env whisky
     */
    public function blackLabel(PowerGuy $I)
    {
        $I->castFireball();
    }

    public function powerOfTheUniverse(PowerGuy $I)
    {
        $I->castFireball();
    }
}
