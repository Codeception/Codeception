<?php

namespace Jazz;

class TestHelper extends \Codeception\Module
{
    public function seeEquals($expected, $actual)
    {
        $this->assertEquals($expected, $actual);
    }
}
