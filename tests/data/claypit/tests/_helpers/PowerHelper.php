<?php
namespace Codeception\Module;

// here you can define custom functions for PowerGuy 

class PowerHelper extends \Codeception\Module
{
    protected $config = array('has_power' => false);

    public function _hasPower()
    {
        return $this->config['has_power'];
    }

    public function gotThePower()
    {
        if (!$this->config['has_power']) $this->fail("I have no power :(");
    }

    public function castFireball()
    {
        $this->assertTrue(true);
    }
}
