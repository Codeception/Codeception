<?php

namespace Codeception\Util;

/**
 * Custom autoloader to load classes by suffixes: `Helper`, `Page`, `Step`, etc.
 *
 */
class Autoload
{
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
     * Autoload::register('app\Codeception\Helper', 'Helper', __DIR__.'/helpers/');
     * // loads LoginPage in 'pageobjects/LoginPage.php'
     * Autoload::register('app\tests', 'Page', __DIR__.'/pageobjects/');
     * Autoload::register('app\tests', 'Controller', __DIR__.'/controllers/');
     * ?>
     * ```
     *
     * @param $namespace
     * @param $suffix
     * @param $path
     */
    public static function register($namespace, $suffix, $path)
    {
        self::$map[] = array(self::regex($namespace, $suffix), $path);
        if (!self::$registered) {
            spl_autoload_register(array(__CLASS__, 'load'));
            self::$registered = true;
        }
    }

    /**
     * Shortcut for {@link self::register} for classes with empty namespaces.
     *
     * @param $suffix
     * @param $path
     */
    public static function registerSuffix($suffix, $path)
    {
        self::register('', $suffix, $path);
    }

    /**
     * @param $class
     * @return bool
     */
    public static function load($class)
    {
        $map = array_reverse(self::$map);
        foreach ($map as $record) {
            list($regex, $path) = $record;
            if (!preg_match($regex, $class)) {
                continue;
            }
            $className = str_replace('\\', '', substr($class, (int)strrpos($class, '\\')));
            $file      = $path . DIRECTORY_SEPARATOR . $className . '.php';
            if (!file_exists($file)) {
                continue;
            }
            include_once $file;
            return true;
        }

        return false;
    }

    /**
     * *is public for testing purposes*
     *
     * @param $class
     * @param $namespace
     * @param $suffix
     * @return bool
     */
    public static function matches($class, $namespace, $suffix)
    {
        return (bool)preg_match(self::regex($namespace, $suffix), $class);
    }

    protected static function regex($namespace, $suffix)
    {
        $namespace = str_replace("\\", '\\\\', $namespace);
        if ($namespace) {
            return sprintf('~\\\\?%s\\\\\w*?%s$~', $namespace, $suffix);
        } else {
            return sprintf('~\w*?%s$~', $suffix);
        }
    }
}
