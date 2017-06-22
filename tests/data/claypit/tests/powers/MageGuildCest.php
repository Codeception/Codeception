<?php
class MageGuildCest
{

    /**
     * @env magic
     * @env dark
     * @param PowerGuy $I
     */
    public function darkPower(PowerGuy $I)
    {
        $I->castFireball();
    }

    /**
     * @env magic
     * @env white
     * @param PowerGuy $I
     */
    public function whitePower(PowerGuy $I)
    {
        $I->castFireball();
    }

    /**
     * @env magic
     * @env green
     * @param PowerGuy $I
     */
    public function greenPower(PowerGuy $I)
    {
        $I->castFireball();
    }

    /**
     * @env whisky
     * @env red
     * @param PowerGuy $I
     */
    public function redLabel(PowerGuy $I)
    {
        $I->castFireball();
    }

    /**
     * @env dark
     * @env whisky
     * @param PowerGuy $I
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