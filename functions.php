<?php

// function not autoloaded in PHP, thus its a good place for them
use Codeception\Extension\Logger;

function codecept_debug(mixed $data): void
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

function codecept_root_dir(string $appendPath = ''): string
{
    return \Codeception\Configuration::projectDir() . $appendPath;
}

function codecept_output_dir(string $appendPath = ''): string
{
    return \Codeception\Configuration::outputDir() . $appendPath;
}

/**
 * @deprecated Use `codecept_output_dir()` instead
 */
function codecept_log_dir(string $appendPath = ''): string
{
    return \Codeception\Configuration::outputDir() . $appendPath;
}


function codecept_data_dir(string $appendPath = ''): string
{
    return \Codeception\Configuration::dataDir() . $appendPath;
}

function codecept_relative_path(string $path): string
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
 * @return string the absolute path
 */
function codecept_absolute_path(string $path): string
{
    return codecept_is_path_absolute($path) ? $path : codecept_root_dir($path);
}

/**
 * Checks whether the given $path is absolute.
 */
function codecept_is_path_absolute(string $path): bool
{
    return \Codeception\Util\PathResolver::isPathAbsolute($path);
}

function codecept_log(): \Monolog\Logger
{
    return Logger::getLogger();
}
