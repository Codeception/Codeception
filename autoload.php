<?php

require_once 'vendor/UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Codeception' => __DIR__ . '/src',
    'Monolog' => __DIR__ . '/vendor',
    'Symfony\Component' => __DIR__ . '/vendor',
));

$loader->register(true);

if (stream_resolve_include_path('PHPUnit/Autoload.php') ) {
    include_once 'PHPUnit/Autoload.php';
} elseif (stream_resolve_include_path('vendor/EHER/PHPUnit/src/phpunit/PHPUnit/Autoload.php')) {
   include_once 'vendor/EHER/PHPUnit/src/phpunit/PHPUnit/Autoload.php';
}

if (stream_resolve_include_path('mink/autoload.php')) {
    include_once 'mink/autoload.php';
} elseif (file_exists('vendor/autoload.php') && !class_exists('Composer\Autoload\ClassLoader')) {
    include_once 'vendor/autoload.php';
}