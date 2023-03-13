<?php

// Here you can initialize variables that will for your tests
\Codeception\Configuration::$lock = true;

\Codeception\Util\Autoload::addNamespace('', 'tests/unit/Codeception/Command');
\Codeception\Util\Autoload::addNamespace('Codeception\Util', 'tests/unit/Codeception/Util');
\Codeception\Util\Autoload::addNamespace('Project\Command', 'tests/data/register_command/examples');
