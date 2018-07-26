<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

use Codeception\TestInterface;

class Dummy extends \Codeception\Module
{
    public function _before(TestInterface $test)
    {
        $this->debug($this->config);
    }

    public function seePathIsSet()
    {
        $this->assertNotEmpty($this->config['path']);
    }

    public function seeVarsAreSet()
    {
        $vars = $this->config['vars'];
        $this->assertContains('val1', $vars);
        $this->assertContains('val2', $vars);
    }
}
