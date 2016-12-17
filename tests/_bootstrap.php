<?php

use Codeception\Configuration;
use Codeception\Util\Autoload;
use TestFramework\Module\src\Modules;

define('PROJECT_ROOT', dirname(__DIR__) . '/');
include PROJECT_ROOT . '/vendor/autoload.php';
Autoload::addNamespace('TestFramework', PROJECT_ROOT . 'tests/support/TestFramework');

$config = Configuration::config();
(new Modules())->setUp($config['environment_modules']);
