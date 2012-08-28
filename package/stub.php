#!/usr/bin/env php
<?php
Phar::mapPhar();

chdir(__DIR__);

require_once 'phar://codecept.phar/autoload.php';
require_once 'phar://codecept.phar/Codeception/Codeception/autoload.php';

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

$loader = new UniversalClassLoader();
$loader->registerPrefix('PHPUnit_','phar://codecept.phar/EHER/PHPUnit/src/phpunit');
$loader->registerPrefixFallbacks(array(
    'phar://codecept.phar/EHER/PHPUnit/src/phpunit-mock-objects',
    'phar://codecept.phar/EHER/PHPUnit/src/php-code-coverage',
    'phar://codecept.phar/EHER/PHPUnit/src/php-file-iterator',
    'phar://codecept.phar/EHER/PHPUnit/src/php-text-template',
    'phar://codecept.phar/EHER/PHPUnit/src/php-timer',
    'phar://codecept.phar/EHER/PHPUnit/src/php-token-stream',
    'phar://codecept.phar/EHER/PHPUnit/src/phpunit-skeleton-generator',

));
$loader->register(true);

require_once 'phar://codecept.phar/codecept';

__HALT_COMPILER();