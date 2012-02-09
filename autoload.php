<?php

require_once 'vendor/UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Codeception' => __DIR__ . '/src',
    'Monolog' => __DIR__ . '/vendor',
    'Symfony\Component' => __DIR__ . '/vendor',
));

$loader->register();

if (stream_resolve_include_path('PHPUnit/Autoload.php') 
		|| file_exists('PHPUnit/Autoload.php')) {
    include_once 'PHPUnit/Autoload.php';
}
if (stream_resolve_include_path('mink/autoload.php') 
		|| file_exists('mink/autoload.php')) {
    include_once 'mink/autoload.php';
}

if ((stream_resolve_include_path('vendor/.composer/autoload.php')
		|| file_exists('vendor/.composer/autoload.php'))
		&& !class_exists('Composer\Autoload\ClassLoader')) {
	include_once 'vendor/.composer/autoload.php';
}
