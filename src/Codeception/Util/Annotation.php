<?php
namespace Codeception\Util;

/**
 * Simple annotation parser. Take only key-value annotations for methods or class.
 */
class Annotation
{
    protected static $reflectedClasses = [];
    protected static $regex = '/@%s(?:[ \t]*(.*?))?[ \t]*(?:\*\/)?\r?$/m';
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

    /**
     * Parses raw comment for annotations
     *
     * @param $docblock
     * @param $annotation
     * @return array
     */
    public static function fetchAnnotationsFromDocblock($annotation, $docblock)
    {
        if (preg_match_all(sprintf(self::$regex, $annotation), $docblock, $matched)) {
            return $matched[1];
        }
        return [];
    }

    /**
     * Fetches all available annotations
     *
     * @param $docblock
     * @return array
     */
    public static function fetchAllAnnotationsFromDocblock($docblock)
    {
        $annotations = [];
        if (!preg_match_all(sprintf(self::$regex, '(\w+)'), $docblock, $matched)) {
            return $annotations;
        }
        foreach ($matched[1] as $k => $annotation) {
            if (!isset($annotations[$annotation])) {
                $annotations[$annotation] = [];
            }
            $annotations[$annotation][] = $matched[2][$k];
        };
        return $annotations;
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

    public function raw()
    {
        return $this->currentReflectedItem->getDocComment();
    }

    /**
     * Returns an associative array value of annotation
     * Either JSON or Doctrine-annotation style allowed
     * Returns null if not a valid array data
     *
     * @param $annotation
     * @return array|mixed|string
     */
    public static function arrayValue($annotation)
    {
        $annotation = trim($annotation);
        $openingBrace = substr($annotation, 0, 1);

        // json-style data format
        if (in_array($openingBrace, ['{', '['])) {
            return json_decode($annotation, true);
        }

        // doctrine-style data format
        if ($openingBrace === '(') {
            preg_match_all('~(\w+)\s*?=\s*?"(.*?)"\s*?[,)]~', $annotation, $matches, PREG_SET_ORDER);
            $data = [];
            foreach ($matches as $item) {
                $data[$item[1]] = $item[2];
            }
            return $data;
        }
        return null;
    }
}
