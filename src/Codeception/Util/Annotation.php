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
        $className = is_object($class) ? $class::class : $class;
        static::$reflectedClasses[$className] ??= new ReflectionClass($className);
        return new self(static::$reflectedClasses[$className]);
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
        return preg_match_all(sprintf(self::$regex, $annotation), $docblock, $m) ? $m[1] : [];
    }

    /**
     * Fetches all available annotations
     */
    public static function fetchAllAnnotationsFromDocblock(string $docblock): array
    {
        if (!preg_match_all(sprintf(self::$regex, '(\w+)'), $docblock, $matched)) {
            return [];
        }

        $annotations = [];
        foreach ($matched[1] as $i => $annotation) {
            $annotations[$annotation][] = $matched[2][$i] ?? '';
        }
        return $annotations;
    }

    public function __construct(ReflectionClass $reflectionClass)
    {
        $this->currentReflectedItem = $this->reflectedClass = $reflectionClass;
    }

    public function method(string $method): self
    {
        $this->currentReflectedItem = $this->reflectedClass->getMethod($method);
        return $this;
    }

    public function fetch(string $annotation): ?string
    {
        if (($attr = $this->attribute($annotation)) instanceof ReflectionAttribute) {
            return $attr->getArguments()[0] ?? '';
        }

        $matches = self::fetchAnnotationsFromDocblock($annotation, (string)$this->currentReflectedItem->getDocComment());
        return $matches[0] ?? null;
    }

    public function fetchAll(string $annotation): array
    {
        if (($attr = $this->attribute($annotation)) instanceof ReflectionAttribute) {
            if (!$attr->isRepeated()) {
                return $attr->getArguments();
            }

            if ($annotation === 'example') {
                $annotation = 'examples';
            }
            $attrClass = "Codeception\\Attribute\\" . ucfirst($annotation);
            $attrs = array_filter($this->attributes(), static fn($a): bool => $a->getName() === $attrClass);

            return $annotation === 'examples'
                ? array_map(static fn($a) => $a->getArguments(), $attrs)
                : array_merge(...array_map(static fn($a) => $a->getArguments(), $attrs));
        }

        return self::fetchAnnotationsFromDocblock($annotation, (string)$this->currentReflectedItem->getDocComment());
    }

    public function attributes(): array
    {
        return array_filter(
            $this->currentReflectedItem->getAttributes(),
            static fn(ReflectionAttribute $a): bool => str_starts_with($a->getName(), 'Codeception\\Attribute\\')
        );
    }

    public function attribute(string $name): ?ReflectionAttribute
    {
        $search = "Codeception\\Attribute\\" . ucfirst($name === 'example' ? 'examples' : $name);
        foreach ($this->attributes() as $attr) {
            if ($attr->getName() === $search) {
                return $attr;
            }
        }
        return null;
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
        $first      = $annotation[0] ?? '';

        if (in_array($first, ['{', '['])) {
            return json_decode($annotation, true, 512, JSON_THROW_ON_ERROR);
        }

        if ($first === '(') {
            preg_match_all('#(\w+)\s*=\s*"(.*?)"\s*[,)]#', $annotation, $matches, PREG_SET_ORDER);
            $data = [];
            foreach ($matches as $item) {
                $data[$item[1]] = $item[2];
            }
            return $data;
        }
        return null;
    }
}
