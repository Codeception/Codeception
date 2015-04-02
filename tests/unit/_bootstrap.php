<?php
// Here you can initialize variables that will for your tests
\Codeception\Configuration::$lock = true;
require_once \Codeception\Configuration::dataDir().'DummyOverloadableClass.php';