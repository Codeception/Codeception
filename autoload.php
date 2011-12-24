<?php

require_once 'vendor/UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Codeception' => __DIR__ . '/src',
    'Monolog' => __DIR__ . '/vendor',
    'Symfony\Component' => __DIR__ . '/vendor',
));

$loader->register();
$loader->registerNamespaceFallbacks(array(__DIR__.'/vendor/Mink/vendor'));

@include_once 'PHPUnit/Autoload.php';
@include_once 'mink/autoload.php';
