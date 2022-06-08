<?php

use Codeception\Attribute\Prepare;

class PowerUpCest
{
    public function iHaveNoPower(PowerGuy $I)
    {
        $I->expectThrowable('Exception', function () use ($I) {
            $I->gotThePower();
        });
    }


    #[Prepare('drinkBluePotion')]
    public function iGotBluePotion(PowerGuy $I)
    {
        $I->gotThePower();
    }

    protected function drinkBluePotion(\Codeception\Module\PowerHelper $helper)
    {
        $helper->_reconfigure(['has_power' => true]);
    }
}
