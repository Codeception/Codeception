<?php

require_once 'vendor/UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Codeception' => __DIR__ . '/src'
));
$loader->register(true);

if (stream_resolve_include_path(__DIR__.'/vendor/autoload.php')) {
    include_once __DIR__.'/vendor/EHER/PHPUnit/src/phpunit/PHPUnit/Autoload.php';
    include_once __DIR__.'/vendor/autoload.php';
}

// hardcode fix to broken goutte. Fuck this composer and friends!
if (!class_exists('Goutte\Client')) {
    $loader->registerNamespace('Goutte','vendor/fabpot/goutte');
    $loader->register(true);
}
