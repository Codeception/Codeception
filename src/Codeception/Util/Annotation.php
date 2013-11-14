<?php


namespace Codeception\Util;


class Annotation {

    protected static $reflectedClasses = array();
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
     * @return $this
     */
    public static function forClass($class)
    {
        if (is_object($class)) $class = get_class($class);
        if (!isset(static::$reflectedClasses[$class])) {
            static::$reflectedClasses[$class] = new \ReflectionClass($class);
        }
        return new static(static::$reflectedClasses[$class]);
    }

    /**
     * @param $class
     * @param $method
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
     * @return $this
     */
    public function method($method)
    {
        $this->currentReflectedItem = $this->reflectedClass->getMethod($method);
        return $this;
    }

    public function fetch($annotation)
    {
        $docBlock = $this->currentReflectedItem->getDocComment();
        $matched = array();
        $res = preg_match(sprintf(self::$regex, $annotation), $docBlock, $matched);
        if (!$res) return null;
        return $matched[1];
    }

    public function fetchAll($annotation)
    {
        $docBlock = $this->currentReflectedItem->getDocComment();
        $matched = array();
        $res = preg_match_all(sprintf(self::$regex, $annotation), $docBlock, $matched);
        if (!$res) return array();
        return $matched[1];
    }

}