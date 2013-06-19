<?php
namespace Codeception\Util;

class Autoload {

    protected static $registered = false;
    protected static $map = array();

    /**
     * A very basic yet useful autoloader, not compatible with PSR-0.
     * It is used to autoload classes by namespaces with suffixes.
     *
     * Example:
     *
     * ``` php
     * <?php
     * // loads UserHelper in 'helpers/UserHelper.php'
     * Autoload::register('app\Codeception\Helper','Helper', __DIR__.'/helpers/');
     * // loads UserHelper in 'helpers/UserHelper.php'
     * Autoload::register('app\tests','Page', __DIR__.'/pageobjects/');
     * Autoload::register('app\tests','Controller', __DIR__.'/controllers/');
     * ?>
     * ```
     *
     * @param $namespace
     * @param $suffix
     * @param $path
     */
    public static function register($namespace, $suffix, $path)
    {
        self::$map[self::regex($namespace, $suffix)] = $path;
        if (!self::$registered) {
            spl_autoload_register(array(__CLASS__, 'autoload'));
            self::$registered = true;
        }
    }

    /**
     * Shortcut for Autoload::register for classes with empty namespaces.
     *
     * @param $suffix
     * @param $path
     */
    public static function registerSuffix($suffix, $path)
    {
        self::register('', $suffix, $path);
    }


    public static function autoload($class)
    {
        foreach (self::$map as $regex => $path) {
            if (!preg_match($regex, $class)) continue;
            $className = str_replace('\\', '', substr($class, (int)strrpos($class, '\\')));
            $file = $path.DIRECTORY_SEPARATOR.$className.'.php';
            if (!file_exists($file)) continue;
            include_once $file;
            return true;
        }
        return false;
    }

    // is public for testing purposes
    public static function matches($class, $namespace, $suffix)
    {
        return (bool) preg_match(self::regex($namespace, $suffix), $class);
    }

    protected static function regex($namespace, $suffix)
    {
        $namespace = str_replace("\\",'\\\\', $namespace);
        return sprintf('~\\\\?%s\\\\\w*?%s$~', $namespace, $suffix);
    }
}
