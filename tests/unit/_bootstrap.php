<?php
// Here you can initialize variables that will for your tests
\Codeception\Configuration::$lock = true;

function make_container()
{
    return \Codeception\Util\Stub::make('Codeception\Lib\ModuleContainer');
}
