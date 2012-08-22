<?php

require_once 'vendor/UniversalClassLoader.php';

$loader = new UniversalClassLoader();
$loader->registerNamespaces(array(
    'Codeception' => __DIR__ . '/src'
));
$loader->register(true);

if (stream_resolve_include_path('PHPUnit/Autoload.php') ) {
    include_once 'PHPUnit/Autoload.php';
    list($major, $minor) = explode('.',\PHPUnit_Runner_Version::id());
    if (!($major >= 3 and $minor >= 6)) throw new Exception("Preinstalled PHPUnit is lower then 3.6. Please update or use PHAR version");
} elseif (stream_resolve_include_path('vendor/EHER/PHPUnit/src/phpunit/PHPUnit/Autoload.php')) {
   include_once 'vendor/EHER/PHPUnit/src/phpunit/PHPUnit/Autoload.php';
}

if (file_exists('vendor/autoload.php') && !class_exists('Composer\Autoload\ClassLoader')) {
    include_once 'vendor/autoload.php';
    // hardcode fix to broken goutte. Fuck this composer and friends!
    if (!class_exists('Goutte\Client')) {
        $loader->registerNamespace('Goutte','vendor/fabpot/goutte');
        $loader->register(true);
    }
}