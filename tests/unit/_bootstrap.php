<?php
// Here you can initialize variables that will for your tests
\Codeception\Configuration::$lock = true;

function make_container()
{
    return \Codeception\Stub::make('Codeception\Lib\ModuleContainer');
}

require_once \Codeception\Configuration::dataDir() . 'DummyOverloadableClass.php';
