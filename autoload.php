<?php
// for phar
if (stream_resolve_include_path('vendor/autoload.php')) {
    $loader = require_once('vendor/autoload.php');
    $loader->add('Codeception', __DIR__ . '/src');
    $loader->register(true);
} elseif (stream_resolve_include_path(__DIR__.'/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
}

/** spike-fix for PHP 5.3 */
if (! interface_exists('JsonSerializable')) {
    interface JsonSerializable {
        function jsonSerialize();
    }
}