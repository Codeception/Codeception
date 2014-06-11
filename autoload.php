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

function codecept_root_dir($appendPath = '')
{
    return \Codeception\Configuration::projectDir() . $appendPath;
}

function codecept_output_dir($appendPath = '')
{
    return \Codeception\Configuration::outputDir() . $appendPath;
}

function codecept_log_dir($appendPath = '')
{
    return \Codeception\Configuration::outputDir() . $appendPath;
}

function codecept_data_dir($appendPath = '')
{
    return \Codeception\Configuration::dataDir() . $appendPath;
}