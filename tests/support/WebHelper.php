<?php
namespace Codeception\Module;

// here you can define custom functions for WebGuy 

class WebHelper extends \Codeception\Module
{
    public function changeBrowser($browser)
    {
        $this->getModule('WebDriver')->_restart(['browser' => $browser]);
    }
}
