<?php

declare(strict_types=1);

namespace Codeception\Util;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use Reflector;

use function in_array;
use function is_object;
use function json_decode;
use function preg_match;
use function preg_match_all;
use function sprintf;
use function substr;
use function trim;

/**
 * Simple annotation parser. Take only key-value annotations for methods or class.
 */
class Annotation
{
    /**
     * @var ReflectionClass[]
     */
    protected static array $reflectedClasses = [];

    protected static string $regex = '/@%s(?:[ \t]*(.*?))?[ \t]*(?:\*\/)?\r?$/m';

    protected ReflectionClass $reflectedClass;

    /**
     * @var ReflectionClass|ReflectionMethod
     */
    protected Reflector $currentReflectedItem;

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
     * ```
     */
    public static function forClass(object|string $class): self
    {
        if (is_object($class)) {
            $class = $class::class;
        }

        if (!isset(static::$reflectedClasses[$class])) {
            static::$reflectedClasses[$class] = new ReflectionClass($class);
        }

        return new self(static::$reflectedClasses[$class]);
    }

    public static function forMethod(object|string $class, string $method): self
    {
        return self::forClass($class)->method($method);
    }

    /**
     * Parses raw comment for annotations
     */
    public static function fetchAnnotationsFromDocblock(string $annotation, string $docblock): array
    {
        if (preg_match_all(sprintf(self::$regex, $annotation), $docblock, $matched)) {
            return $matched[1];
        }
        return [];
    }

    /**
     * Fetches all available annotations
     */
    public static function fetchAllAnnotationsFromDocblock(string $docblock): array
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
        }
        return $annotations;
    }

    public function __construct(ReflectionClass $reflectionClass)
    {
        $this->currentReflectedItem = $reflectionClass;
        $this->reflectedClass = $reflectionClass;
    }

    public function method(string $method): self
    {
        $this->currentReflectedItem = $this->reflectedClass->getMethod($method);
        return $this;
    }

    public function fetch(string $annotation): ?string
    {
        $attr = $this->attribute($annotation);
        if ($attr instanceof ReflectionAttribute) {
            $arguments = $attr->getArguments();
            if ($arguments === []) {
                return '';
            }
            return $arguments[0];
        }
        $docBlock = (string)$this->currentReflectedItem->getDocComment();
        if (preg_match(sprintf(self::$regex, $annotation), $docBlock, $matched)) {
            return $matched[1];
        }
        return null;
    }

    public function fetchAll(string $annotation): array
    {
        $attr = $this->attribute($annotation);
        if ($attr instanceof ReflectionAttribute) {
            if (!$attr->isRepeated()) {
                return $attr->getArguments();
            }
            $attrs = $this->attributes();
            if ($annotation === 'example') {
                $annotation = 'examples'; // we renamed this annotation
            }
            $name = ucfirst($annotation);
            $attrs = array_filter($attrs, fn ($a): bool => $a->getName() === "Codeception\\Attribute\\$name");
            if ($annotation === 'examples') {
                return array_map(fn (ReflectionAttribute $a): array => $a->getArguments(), $attrs);
            }
            return array_merge(...array_map(fn (ReflectionAttribute $a): array => $a->getArguments(), $attrs));
        }
        $docBlock = (string)$this->currentReflectedItem->getDocComment();
        if (preg_match_all(sprintf(self::$regex, $annotation), $docBlock, $matched)) {
            return $matched[1];
        }
        return [];
    }

    public function attributes(): array
    {
        $attrs = $this->currentReflectedItem->getAttributes();
        $attrs = array_filter($attrs);
        return array_filter($attrs, fn (ReflectionAttribute $a): bool => str_starts_with($a->getName(), 'Codeception\\Attribute\\'));
    }

    public function attribute(string $name): ?ReflectionAttribute
    {
        $attrs = $this->attributes();
        if ($name === 'example') {
            $name = 'examples'; // we renamed this annotation
        }
        $name = ucfirst($name);
        $attrs = array_filter($attrs, fn ($a): bool => $a->getName() === "Codeception\\Attribute\\$name");
        if ($attrs === []) {
            return null;
        }
        return reset($attrs);
    }

    public function raw(): string|false
    {
        return $this->currentReflectedItem->getDocComment();
    }

    /**
     * Returns an associative array value of annotation
     * Either JSON or Doctrine-annotation style allowed
     * Returns null if not a valid array data
     */
    public static function arrayValue(string $annotation): ?array
    {
        $annotation = trim($annotation);
        $openingBrace = substr($annotation, 0, 1);

        // json-style data format
        if (in_array($openingBrace, ['{', '['])) {
            return json_decode($annotation, true, 512, JSON_THROW_ON_ERROR);
        }

        // doctrine-style data format
        if ($openingBrace === '(') {
            preg_match_all('#(\w+)\s*?=\s*?"(.*?)"\s*?[,)]#', $annotation, $matches, PREG_SET_ORDER);
            $data = [];
            foreach ($matches as $item) {
                $data[$item[1]] = $item[2];
            }
            return $data;
        }
        return null;
    }
}
