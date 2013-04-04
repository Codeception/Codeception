<?php

if (stream_resolve_include_path(__DIR__ . '/vendor/autoload.php')) {
    require_once __DIR__ . 'vendor/autoload.php';
} elseif (stream_resolve_include_path(__DIR__.'/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
}
