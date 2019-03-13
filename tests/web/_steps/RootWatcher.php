<?php
namespace WebGuy\Steps;

class RootWatcher extends \WebGuy
{
    public function seeInRootPage($selector)
    {
        $I = $this;
        $I->amOnPage('/');
        $I->see($selector);
    }
}
