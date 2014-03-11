<?php
// for phar
if (file_exists(__DIR__.'/vendor/autoload.php')) {
    $loader = require_once(__DIR__.'/vendor/autoload.php');
    $loader->add('Codeception', __DIR__ . '/src');
    $loader->register(true);
} elseif (file_exists(__DIR__.'/../../autoload.php')) {
    require_once __DIR__ . '/../../autoload.php';
}

// function not autoloaded in PHP, thus its a good place for them
function codecept_debug($data)
{
    \Codeception\Util\Debug::debug($data);
}

function codecept_root_dir()
{
    return \Codeception\Configuration::projectDir();
}

function codecept_log_dir()
{
    return \Codeception\Configuration::logDir();
}

function codecept_data_dir()
{
    return \Codeception\Configuration::dataDir();
}