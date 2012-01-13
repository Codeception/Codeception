<?php

require_once 'vendor/UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Codeception' => __DIR__ . '/src',
    'Monolog' => __DIR__ . '/vendor',
    'Symfony\Component' => __DIR__ . '/vendor',
));

$loader->register();

@include_once 'PHPUnit/Autoload.php';
@include_once 'mink/autoload.php';

if (!class_exists('Behat\Behat\Mink')) {
    include_once 'vendor/.composer/autoload.php';
}