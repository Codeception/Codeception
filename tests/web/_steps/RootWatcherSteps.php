<?php
namespace WebGuy;

class RootWatcherSteps extends \WebGuy
{
    public function seeInRootPage($selector)
    {
        $I = $this;
        $I->amOnPage('/');
        $I->see($selector);
    }
}
