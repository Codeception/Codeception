<?php

// function not autoloaded in PHP, thus its a good place for them
use Codeception\Extension\Logger;

function codecept_debug($data)
{
    \Codeception\Util\Debug::debug($data);
}

/**
 * Executes interactive pause in ths place
 *
 * @param array $vars
 * @return void
 */
function codecept_pause(array $vars = []): void
{
    \Codeception\Util\Debug::pause($vars);
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

function codecept_relative_path($path)
{
    return \Codeception\Util\PathResolver::getRelativeDir(
        $path,
        \Codeception\Configuration::projectDir(),
        DIRECTORY_SEPARATOR
    );
}

/**
 * If $path is absolute, it will be returned without changes.
 * If $path is relative, it will be passed to `codecept_root_dir()` function
 * to make it absolute.
 *
 * @param string $path
 * @return string the absolute path
 */
function codecept_absolute_path($path)
{
    return codecept_is_path_absolute($path) ? $path : codecept_root_dir($path);
}

/**
 * Check whether the given $path is absolute.
 *
 * @param string $path
 * @return bool
 * @since 2.4.4
 */
function codecept_is_path_absolute($path)
{
    return \Codeception\Util\PathResolver::isPathAbsolute($path);
}

function codecept_log(): \Monolog\Logger
{
    return Logger::getLogger();
}
