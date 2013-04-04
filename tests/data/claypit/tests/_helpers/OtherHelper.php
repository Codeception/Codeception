<?php
namespace Codeception\Module;


class OtherHelper extends \Codeception\Module
{
    public function _before(\Codeception\TestCase $test)
    {
        if (strpos(PHP_VERSION, '5.3')===0) $test->markTestSkipped();
    }

}