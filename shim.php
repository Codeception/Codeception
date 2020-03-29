<?php
// @codingStandardsIgnoreStart

namespace Symfony\Component\CssSelector {
    if (!class_exists('Symfony\Component\CssSelector\CssSelectorConverter')) {
        class CssSelectorConverter {
            function toXPath($cssExpr, $prefix = 'descendant-or-self::') {
                return CssSelector::toXPath($cssExpr, $prefix);
            }
        }
    }
}


// prefering old names

namespace Codeception\TestCase {

    class Test extends \Codeception\Test\Unit {
    }
}

namespace Codeception\Platform {
    abstract class Group extends \Codeception\GroupObject
    {
    }
    abstract class Extension extends \Codeception\Extension
    {
    }
}

namespace {
    class_alias('Codeception\TestInterface', 'Codeception\TestCase');

    //Compatibility with Symfony 5
    if (!class_exists('Symfony\Component\EventDispatcher\Event') && class_exists('Symfony\Contracts\EventDispatcher\Event')) {
        class_alias('Symfony\Contracts\EventDispatcher\Event', 'Symfony\Component\EventDispatcher\Event');
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
            return \Codeception\Util\PathResolver::isPathAbsolute($path);
        }
    }
}
