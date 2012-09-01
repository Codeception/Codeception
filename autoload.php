<?php

require_once 'vendor/UniversalClassLoader.php';

$namespaceMap = require_once __DIR__ . '/vendor/composer/autoload_namespaces.php';
$prefixesMap = require_once __DIR__ . '/vendor/composer/autoload_classmap.php';

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Codeception' => __DIR__ . '/src'
));
$loader->registerNamespaces($namespaceMap);
$loader->registerPrefixes($prefixesMap);
$loader->register(true);

if (stream_resolve_include_path(__DIR__.'/vendor/autoload.php')) {
    include_once __DIR__.'/vendor/EHER/PHPUnit/src/phpunit/PHPUnit/Autoload.php';
}

// hardcode fix to broken goutte. Fuck this composer and friends!
if (!class_exists('Goutte\Client')) {
    $loader->registerNamespace('Goutte','vendor/fabpot/goutte');
    $loader->register(true);
}
