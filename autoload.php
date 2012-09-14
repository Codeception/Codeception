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

if (!function_exists('phpunit_autoload')) {
    function phpunit_autoload() { return array(); };
}
if (!function_exists('phpunit_mockobject_autoload')) {
    function phpunit_mockobject_autoload() { return array(); } ;
}
if (!function_exists('file_iterator_autoload')) {
    function file_iterator_autoload() { return array(); } ;
}
if (!function_exists('php_codecoverage_autoload')) {
    function php_codecoverage_autoload() { return array(); };
}
if (!function_exists('php_timer_autoload')) {
    function php_timer_autoload() { return array(); };
}
if (!function_exists('php_tokenstream_autoload')) {
    function php_tokenstream_autoload() { return array(); };
}
if (!function_exists('text_template_autoload')) {
    function text_template_autoload() { return array(); };
}

$loader->registerPrefix('PHPUnit_', __DIR__.'/vendor/EHER/PHPUnit/src/phpunit');
$loader->registerPrefixFallbacks(array(
    __DIR__.'/vendor/EHER/PHPUnit/src/phpunit-mock-objects',
    __DIR__.'/vendor/EHER/PHPUnit/src/php-code-coverage',
    __DIR__.'/vendor/EHER/PHPUnit/src/php-file-iterator',
    __DIR__.'/vendor/EHER/PHPUnit/src/php-text-template',
    __DIR__.'/vendor/EHER/PHPUnit/src/php-timer',
    __DIR__.'/vendor/EHER/PHPUnit/src/php-token-stream',
    __DIR__.'/vendor/EHER/PHPUnit/src/phpunit-skeleton-generator',
));


$loader->register(true);

// hardcode fix to broken goutte. Fuck this composer and friends!
if (!class_exists('Goutte\Client')) {
    $loader->registerNamespace('Goutte','vendor/fabpot/goutte');
    $loader->register(true);
}
