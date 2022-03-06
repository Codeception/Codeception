<?php

namespace Jazz\Pianist;

class TestHelper extends \Codeception\Module
{
    public function seeEquals($expected, $actual)
    {
        $this->assertEquals($expected, $actual);
    }
}
