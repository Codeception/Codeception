<?php

$autoloadFile = './vendor/codeception/codeception/autoload.php';
if (file_exists('./vendor/autoload.php') && file_exists($autoloadFile) && __FILE__ != realpath($autoloadFile)) {
    //for global installation or phar file
    fwrite(
        STDERR,
        "\n==== Redirecting to Composer-installed version in vendor/codeception ====\n"
    );
    require $autoloadFile;
    //require package/bin instead of codecept to avoid printing hashbang line
    require './vendor/codeception/codeception/package/bin';
    die;
} elseif (file_exists(__DIR__ . '/vendor/autoload.php')) {
    // for phar
    require_once(__DIR__ . '/vendor/autoload.php');
} elseif (file_exists(__DIR__ . '/../../autoload.php')) {
    //for composer
    require_once __DIR__ . '/../../autoload.php';
}
unset($autoloadFile);

// @codingStandardsIgnoreStart

include_once __DIR__ . DIRECTORY_SEPARATOR . 'shim.php';
// compat
if (PHP_MAJOR_VERSION < 7) {
    if (false === interface_exists('Throwable', false)) {
        interface Throwable {};
    }
    if (false === class_exists('ParseError', false)) {
        class ParseError extends \Exception {};
    }
}
// @codingStandardsIgnoreEnd

if (!function_exists('json_last_error_msg')) {
    /**
     * Copied from http://php.net/manual/en/function.json-last-error-msg.php#117393
     * @return string
     */
    function json_last_error_msg()
    {
        static $errors = array(
            JSON_ERROR_NONE => 'No error',
            JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
            JSON_ERROR_STATE_MISMATCH => 'State mismatch (invalid or malformed JSON)',
            JSON_ERROR_CTRL_CHAR => 'Control character error, possibly incorrectly encoded',
            JSON_ERROR_SYNTAX => 'Syntax error',
            JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded'
        );

        $error = json_last_error();
        return isset($errors[$error]) ? $errors[$error] : 'Unknown error';
    }
}

// function not autoloaded in PHP, thus its a good place for them
if (!function_exists('codecept_debug')) {
    function codecept_debug($data)
    {
        \Codeception\Util\Debug::debug($data);
    }
}

if (!function_exists('codecept_root_dir')) {
    function codecept_root_dir($appendPath = '')
    {
        return \Codeception\Configuration::projectDir() . $appendPath;
    }
}

if (!function_exists('codecept_output_dir')) {
    function codecept_output_dir($appendPath = '')
    {
        return \Codeception\Configuration::outputDir() . $appendPath;
    }
}

if (!function_exists('codecept_log_dir')) {
    function codecept_log_dir($appendPath = '')
    {
        return \Codeception\Configuration::outputDir() . $appendPath;
    }
}

if (!function_exists('codecept_data_dir')) {
    function codecept_data_dir($appendPath = '')
    {
        return \Codeception\Configuration::dataDir() . $appendPath;
    }
}

if (!function_exists('codecept_relative_path')) {
    function codecept_relative_path($path)
    {
        return \Codeception\Util\PathResolver::getRelativeDir(
            $path,
            \Codeception\Configuration::projectDir(),
            DIRECTORY_SEPARATOR
        );
    }
}

if (!function_exists('codecept_absolute_path')) {
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
}

if (!function_exists('codecept_is_path_absolute')) {
    /**
     * Check whether the given $path is absolute.
     *
     * @param string $path
     * @return bool
     * @since 2.4.4
     */
    function codecept_is_path_absolute($path)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return mb_substr($path, 0, 1) === DIRECTORY_SEPARATOR;
        }

        return preg_match('#^[A-Z]:(?![^/\\])#i', $path) === 1;
    }
}
