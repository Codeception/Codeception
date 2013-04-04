<?php

/** spike-fix for PHP 5.3 */
if (! interface_exists('JsonSerializable')) {
    interface JsonSerializable {
        function jsonSerialize();
    }
}

if (stream_resolve_include_path(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . '/vendor/autoload.php';
} elseif (stream_resolve_include_path(__DIR__.'/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
}
