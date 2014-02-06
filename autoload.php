<?php
// for phar
if (file_exists(__DIR__.'/vendor/autoload.php')) {
    $loader = require_once(__DIR__.'/vendor/autoload.php');
    $loader->add('Codeception', __DIR__ . '/src');
    $loader->register(true);
} elseif (file_exists(__DIR__.'/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
}

// function not autoloaded in PHP, thus its a good place for it
function codecept_debug($data)
{
//    if (!class_exists('\Codeception\Util\Debug')) {
//        return;
//    }
    \Codeception\Util\Debug::debug($data);
}