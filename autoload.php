<?php

require_once __DIR__ . '/vendor/UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Codeception' => __DIR__ . '/src'
));
$loader->register(true);

include_once __DIR__ . '/vendor/EHER/PHPUnit/src/phpunit/PHPUnit/Autoload.php';
include_once __DIR__ . '/vendor/autoload.php';

// hardcode fix to broken goutte. Fuck this composer and friends!
if (!class_exists('Goutte\Client')) {
    $loader->registerNamespace('Goutte', 'vendor/fabpot/goutte');
    $loader->register(true);
}
