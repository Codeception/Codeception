<?php
namespace Codeception\Util;

/**
 * Autoloader, which is fully compatible with PSR-4,
 * and can be used to autoload your `Helper`, `Page`, and `Step` classes.
 */
class Autoload
{
    protected static $registered = false;
    /**
     * An associative array where the key is a namespace prefix and the value
     * is an array of base directories for classes in that namespace.
     * @var array
     */
    protected static $map = [];

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
     * ?>
     * ```
     *
     * @param string $prefix The namespace prefix.
     * @param string $base_dir A base directory for class files in the namespace.
     * @param bool $prepend If true, prepend the base directory to the stack instead of appending it;
     *                      this causes it to be searched first rather than last.
     * @return void
     */
    public static function addNamespace($prefix, $base_dir, $prepend = false)
    {
        if (!self::$registered) {
            spl_autoload_register([__CLASS__, 'load']);
            self::$registered = true;
        }

        // normalize namespace prefix
        $prefix = trim($prefix, '\\') . '\\';

        // normalize the base directory with a trailing separator
        $base_dir = rtrim($base_dir, '/') . DIRECTORY_SEPARATOR;
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';

        // initialize the namespace prefix array
        if (isset(self::$map[$prefix]) === false) {
            self::$map[$prefix] = [];
        }

        // retain the base directory for the namespace prefix
        if ($prepend) {
            array_unshift(self::$map[$prefix], $base_dir);
        } else {
            self::$map[$prefix][] = $base_dir;
        }
    }

    public static function load($class)
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
        if (isset(self::$map['\\']) && ($class[0] != '\\')) {
            return self::load('\\' . $class);
        }

        // backwards compatibility with old autoloader
        // :TODO: it should be removed
        if (strpos($class, '\\') !== false) {
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
     * @param string $relative_class The relative class name.
     * @return mixed Boolean false if no mapped file can be loaded, or the name of the mapped file that was loaded.
     */
    protected static function loadMappedFile($prefix, $relative_class)
    {
        if (!isset(self::$map[$prefix])) {
            return false;
        }

        foreach (self::$map[$prefix] as $base_dir) {
            $file = $base_dir
                . str_replace('\\', '/', $relative_class)
                . '.php';

            // 'static' is for testing purposes
            if (static::requireFile($file)) {
                return $file;
            }
        }

        return false;
    }

    protected static function requireFile($file)
    {
        if (file_exists($file)) {
            require_once $file;
            return true;
        }
        return false;
    }
}
