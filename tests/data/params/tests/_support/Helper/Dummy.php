<?php

namespace Helper;

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

    public function seeVarEquals(int $position, $value)
    {
        $this->assertSame($value, $this->config['vars'][$position]);
    }
}
