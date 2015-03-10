<?php

namespace Codeception\Util;

/**
 * Simple annotation parser. Take only key-value annotations for methods or class.
 */
class Annotation
{
    protected static $reflectedClasses = [];
    protected static $regex = '/@%s(?:[ \t]+(.*?))?[ \t]*\r?$/m';
    protected static $lastReflected = null;

    /**
     * @var \ReflectionClass
     */
    protected $reflectedClass;

    protected $currentReflectedItem;

    /**
     * Grabs annotation values.
     *
     * Usage example:
     *
     * ``` php
     * <?php
     * Annotation::forClass('MyTestCase')->fetch('guy');
     * Annotation::forClass('MyTestCase')->method('testData')->fetch('depends');
     * Annotation::forClass('MyTestCase')->method('testData')->fetchAll('depends');
     *
     * ?>
     * ```
     *
     * @param $class
     *
     * @return $this
     */
    public static function forClass($class)
    {
        if (is_object($class)) {
            $class = get_class($class);
        }

        if (!isset(static::$reflectedClasses[$class])) {
            static::$reflectedClasses[$class] = new \ReflectionClass($class);
        }

        return new static(static::$reflectedClasses[$class]);
    }

    /**
     * @param $class
     * @param $method
     *
     * @return $this
     */
    public static function forMethod($class, $method)
    {
        return self::forClass($class)->method($method);
    }

    public function __construct(\ReflectionClass $class)
    {
        $this->currentReflectedItem = $this->reflectedClass = $class;
    }

    /**
     * @param $method
     *
     * @return $this
     */
    public function method($method)
    {
        $this->currentReflectedItem = $this->reflectedClass->getMethod($method);
        return $this;
    }

    /**
     * @param $annotation
     * @return null
     */
    public function fetch($annotation)
    {
        $docBlock = $this->currentReflectedItem->getDocComment();
        if (preg_match(sprintf(self::$regex, $annotation), $docBlock, $matched)) {
            return $matched[1];
        }
        return null;
    }

    /**
     * @param $annotation
     * @return array
     */
    public function fetchAll($annotation)
    {
        $docBlock = $this->currentReflectedItem->getDocComment();
        if (preg_match_all(sprintf(self::$regex, $annotation), $docBlock, $matched)) {
            return $matched[1];
        }
        return [];
    }
}