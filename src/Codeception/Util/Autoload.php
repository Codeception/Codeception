<?php

declare(strict_types=1);

namespace Codeception\Util;

use function array_unshift;
use function file_exists;
use function rtrim;
use function spl_autoload_register;
use function str_replace;
use function trim;

/**
 * Autoloader, which is fully compatible with PSR-4,
 * and can be used to autoload your `Helper`, `Page`, and `Step` classes.
 */
class Autoload
{
    protected static bool $registered = false;

    /**
     * An associative array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     */
    protected static array $map = [];

    private function __construct()
    {
    }

    /**
     * Adds a base directory for a namespace prefix.
     *
     * Example:
     *
     * ```php
     * <?php
     * // app\Codeception\UserHelper will be loaded from '/path/to/helpers/UserHelper.php'
     * Autoload::addNamespace('app\Codeception', '/path/to/helpers');
     *
     * // LoginPage will be loaded from '/path/to/pageobjects/LoginPage.php'
     * Autoload::addNamespace('', '/path/to/pageobjects');
     *
     * Autoload::addNamespace('app\Codeception', '/path/to/controllers');
     * ```
     *
     * @param string $prefix The namespace prefix.
     * @param string $baseDir A base directory for class files in the namespace.
     * @param bool $prepend If true, prepend the base directory to the stack instead of appending it;
     *                      this causes it to be searched first rather than last.
     */
    public static function addNamespace(string $prefix, string $baseDir, bool $prepend = false): void
    {
        if (!self::$registered) {
            spl_autoload_register(fn(string $class): string|false => self::load($class));
            self::$registered = true;
        }

        // normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        // normalize the base directory with a trailing separator
        $baseDir = rtrim($baseDir, '/') . DIRECTORY_SEPARATOR;
        $baseDir = rtrim($baseDir, DIRECTORY_SEPARATOR) . '/';

        // initialize the namespace prefix array
        if (!isset(self::$map[$prefix])) {
            self::$map[$prefix] = [];
        }

        // retain the base directory for the namespace prefix
        if ($prepend) {
            array_unshift(self::$map[$prefix], $baseDir);
        } else {
            self::$map[$prefix][] = $baseDir;
        }
    }

    public static function load(string $class): string|false
    {
        // the current namespace prefix
        $prefix = $class;

        // work backwards through the namespace names of the fully-qualified class name to find a mapped file name
        while (false !== ($pos = strrpos($prefix, '\\'))) {
            // retain the trailing namespace separator in the prefix
            $prefix = substr($class, 0, $pos + 1);

            // the rest is the relative class name
            $relative_class = substr($class, $pos + 1);

            // try to load a mapped file for the prefix and relative class
            $mapped_file = self::loadMappedFile($prefix, $relative_class);
            if ($mapped_file) {
                return $mapped_file;
            }

            // remove the trailing namespace separator for the next iteration of strrpos()
            $prefix = rtrim($prefix, '\\');
        }

        // fix for empty prefix
        if (isset(self::$map['\\']) && ($class[0] !== '\\')) {
            return self::load('\\' . $class);
        }

        if (str_contains($class, '\\')) {
            $relative_class = substr(strrchr($class, '\\'), 1); // Foo\Bar\ClassName -> ClassName
            $mapped_file = self::loadMappedFile('\\', $relative_class);
            if ($mapped_file) {
                return $mapped_file;
            }
        }

        return false;
    }

    /**
     * Load the mapped file for a namespace prefix and relative class.
     *
     * @param string $prefix The namespace prefix.
     * @param string $relativeClass The relative class name.
     * @return string|false Boolean false if no mapped file can be loaded, or the name of the mapped file that was loaded.
     */
    protected static function loadMappedFile(string $prefix, string $relativeClass): string|false
    {
        if (!isset(self::$map[$prefix])) {
            return false;
        }

        foreach (self::$map[$prefix] as $baseDir) {
            $file = $baseDir
                . str_replace('\\', '/', $relativeClass)
                . '.php';

            // 'static' is for testing purposes
            if (static::requireFile($file)) {
                return $file;
            }
        }

        return false;
    }

    protected static function requireFile($file): bool
    {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }
}
