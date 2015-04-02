<?php
// Here you can initialize variables that will for your tests
require_once \Codeception\Configuration::dataDir().'DummyClass.php';
$overload = \Codeception\Configuration::dataDir().'DummyOverloadableClass.php';
if (file_exists($overload)) {
    require_once($overload);
}
$codeception = 'codeception.yml';