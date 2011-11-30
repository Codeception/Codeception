#!/usr/bin/env php
<?php
Phar::mapPhar();

require_once 'phar://codecept.phar/vendor/UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Codeception' => 'phar://codecept.phar/src/',
    'Monolog' => 'phar://codecept.phar/vendor',
    'Symfony\Component' => 'phar://codecept.phar/vendor',
));

$loader->register();
$loader->registerNamespaceFallbacks(array(__DIR__.'/vendor/Mink/vendor'));

// loading stub generators
require_once 'phar://codecept.phar/src/Codeception/Util/Stub/builders/phpunit/Stub.php';

@include_once 'mink/autoload.php';
@include_once 'PHPUnit/Autoload.php';

use Symfony\Component\Console\Application,
    Symfony\Component\Console\Input\InputInterface,
    Symfony\Component\Console\Input\InputDefinition,
    Symfony\Component\Console\Input\InputOption;

require_once '../codecept';

__HALT_COMPILER();