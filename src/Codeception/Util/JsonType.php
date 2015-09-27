<?php
namespace Codeception\Util;

/**
 * JsonType matches JSON structures against templates.
 * You can specify the type of fields in JSON or add additional validation rules.
 *
 * JsonType is used by REST module in `seeResponseMatchesJsonType` and `dontSeeResponseMatchesJsonType` methods.
 *
 * Usage example:
 *
 * ```php
 * <?php
 * $jsonType = new JsonType(['name' => 'davert', 'id' => 1]);
 * $jsonType->matches([
 *   'name' => 'string:!empty',
 *   'id' => 'integer:>0|string:>0',
 * ])); // => true
 *
 * $jsonType->matches([
 *   'id' => 'string',
 * ])); // => `id: 1` is not of type string
 * ?>
 * ```
 *
 * Class JsonType
 * @package Codeception\Util
 */
class JsonType
{
    protected $jsonArray;

    protected static $customFilters = [];

    /**
     * Creates instance of JsonType
     * Pass an array or `\Codeception\Util\JsonArray` with data.
     * If non-associative array is passed - the very first element of it will be used for matching.
     *
     * @param $jsonArray array|\Codeception\Util\JsonArray
     */
    public function __construct($jsonArray)
    {
        if ($jsonArray instanceof JsonArray) {
            $jsonArray = $jsonArray->toArray();
        }
        $this->jsonArray = $jsonArray;
    }

    /**
     * Adds custom filter to JsonType list.
     * You should specify a name and parameters of a filter.
     *
     * Example:
     *
     * ```php
     * <?php
     * JsonType::addCustomFilter('email', function($value) {
     *     return strpos('@', $value) !== false;
     * });
     * // => use it as 'string:email'

     *
     * // add custom function to matcher with `len($val)` syntax
     * // parameter matching patterns should be valid regex and start with `/` char
     * JsonType::addCustomFilter('/len\((.*?)\)/', function($value, $len) {
     *   return strlen($value) == $len;
     * });
     * // use it as 'string:len(5)'
     * ?>
     * ```
     *
     * @param $name
     * @param callable $callable
     */
    public static function addCustomFilter($name, callable $callable)
    {
        static::$customFilters[$name] = $callable;
    }

    /**
     * Removes all custom filters
     */
    public static function cleanCustomFilters()
    {
        static::$customFilters = [];
    }

    /**
     * Checks data against passed JsonType.
     * If matching fails function returns a string with a message describing failure.
     * On success returns `true`.
     *
     * @param array $jsonType
     * @return bool|string
     */
    public function matches(array $jsonType)
    {
        $data = $this->jsonArray;
        if (array_key_exists(0, $this->jsonArray)) {
            // sequential array
            $data = reset($this->jsonArray);
        }
        return $this->typeComparison($data, $jsonType);
    }

    protected function typeComparison($data, $jsonType)
    {
        foreach ($jsonType as $key => $type) {
            if (!array_key_exists($key, $data)) {
                return "Key `$key` doesn't exist in " . json_encode($data);
            }
            if (is_array($jsonType[$key])) {
                $message = $this->typeComparison($data[$key], $jsonType[$key]);
                if (is_string($message)) {
                    return $message;
                }
                continue;
            }
            $matchTypes = explode('|', $type);
            $matched = false;
            foreach ($matchTypes as $matchType) {
                $currentType = strtolower(gettype($data[$key]));
                if ($currentType == 'double') {
                    $currentType = 'float';
                }
                $filters = explode(':', $matchType);
                $expectedType = trim(strtolower(array_shift($filters)));

                if ($expectedType != $currentType) {
                    break;
                }
                if (empty($filters)) {
                    $matched = true;
                    break;
                }
                foreach ($filters as $filter) {
                    $matched = $this->matchFilter($filter, $data[$key]);
                }
            }
            if (!$matched) {
                return sprintf("`$key: %s` is not of type `$type`", var_export($data[$key], true));
            }
        }
        return true;
    }

    protected function matchFilter($filter, $value)
    {
        $filter = trim($filter);
        if (strpos($filter,'!') === 0) {
            return !$this->matchFilter(substr($filter, 1), $value);
        }

        // apply custom filters
        foreach (static::$customFilters as $customFilter => $callable) {
            if (strpos($customFilter, '/') === 0) {
                if (preg_match($customFilter, $filter, $matches)) {
                    array_shift($matches);
                    return call_user_func_array($callable, array_merge([$value], $matches));
                }
            }
            if ($customFilter == $filter) {
                return $callable($value);
            }
        }

        if (strpos($filter, '=') === 0) {
            return $value == substr($filter, 1);
        }
        if ($filter === 'url') {
            return preg_match('/^(https?:\/\/)?([\da-z\.-]+)\.([a-z\.]{2,6})([\/\w \.-]*)*\/?$/', $value);
        }
        if ($filter === 'date') {
            return preg_match('/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(?:Z|(\+|-)([\d|:]*))?$/', $value);
        }
        if ($filter === 'empty') {
            return empty($value);
        }
        if (preg_match('~^regex\((.*?)\)$~', $filter, $matches)) {
            return preg_match($matches[1], $value);
        }
        if (preg_match('~^>([\d\.]+)$~', $filter, $matches)) {
            return (float)$value > (float)$matches[1];
        }
        if (preg_match('~^<([\d\.]+)$~', $filter, $matches)) {
            return (float)$value < (float)$matches[1];
        }
    }

}