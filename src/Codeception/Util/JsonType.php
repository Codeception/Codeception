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
 * ]); // => true
 *
 * $jsonType->matches([
 *   'id' => 'string',
 * ]); // => `id: 1` is not of type string
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
     * JsonType::addCustomFilter('slug', function($value) {
     *     return strpos(' ', $value) !== false;
     * });
     * // => use it as 'string:slug'
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
     * @param          $name
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
        if (array_key_exists(0, $this->jsonArray) && is_array($this->jsonArray[0])) {
            // a list of items
            $msg = '';
            foreach ($this->jsonArray as $array) {
                $res = $this->typeComparison($array, $jsonType);
                if ($res !== true) {
                    $msg .= "\n" . $res;
                }
            }
            if ($msg) {
                return $msg;
            }
            return true;
        }
        return $this->typeComparison($this->jsonArray, $jsonType);
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

            $regexMatcher = '/:regex\((((\()|(\{)|(\[)|(<)|(.)).*?(?(3)\)|(?(4)\}|(?(5)\]|(?(6)>|\7)))))\)/';
            $regexes = [];

            // Match the string ':regex(' and any characters until a ending regex delimiter followed by character ')'
            // Place the 'any character' + delimiter matches in to an array.
            preg_match_all($regexMatcher, $type, $regexes);

            // Do the same match as above, but replace the the 'any character' + delimiter with a place holder ($${count}).
            $filterType = preg_replace_callback($regexMatcher, function () {
                static $count = 0;
                return ':regex($$' . $count++ . ')';
            }, $type);

            $matchTypes  = preg_split("#(?![^]\(]*\))\|#", $filterType);
            $matched     = false;
            $currentType = strtolower(gettype($data[$key]));

            if ($currentType === 'double') {
                $currentType = 'float';
            }

            foreach ($matchTypes as $matchType) {
                $filters      = preg_split("#(?![^]\(]*\))\:#", $matchType);
                $expectedType = strtolower(trim(array_shift($filters)));

                if ($expectedType !== $currentType) {
                    continue;
                }

                $matched = true;

                foreach ($filters as $filter) {
                    // Fill regex pattern back into the filter.
                    $filter = preg_replace_callback('/\$\$\d+/', function ($m) use ($regexes) {
                        $pos = (int)substr($m[0], 2);

                        return $regexes[1][$pos];
                    }, $filter);

                    $matched = $matched && $this->matchFilter($filter, $data[$key]);
                }

                if ($matched) {
                    break;
                }
            }

            if (!$matched) {
                return sprintf("`$key: %s` is of type `$type`", var_export($data[$key], true));
            }
        }
        return true;
    }

    protected function matchFilter($filter, $value)
    {
        $filter = trim($filter);
        if (strpos($filter, '!') === 0) {
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
            return filter_var($value, FILTER_VALIDATE_URL);
        }
        if ($filter === 'date') {
            return preg_match(
                '/^(\d{4})-(\d{2})-(\d{2})T(\d{2}):(\d{2}):(\d{2})(\.\d+)?(?:Z|(\+|-)([\d|:]*))?$/',
                $value
            );
        }
        if ($filter === 'email') { // from http://emailregex.com/
            // @codingStandardsIgnoreStart
            return preg_match('/^(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){255,})(?!(?:(?:\x22?\x5C[\x00-\x7E]\x22?)|(?:\x22?[^\x5C\x22]\x22?)){65,}@)(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22))(?:\.(?:(?:[\x21\x23-\x27\x2A\x2B\x2D\x2F-\x39\x3D\x3F\x5E-\x7E]+)|(?:\x22(?:[\x01-\x08\x0B\x0C\x0E-\x1F\x21\x23-\x5B\x5D-\x7F]|(?:\x5C[\x00-\x7F]))*\x22)))*@(?:(?:(?!.*[^.]{64,})(?:(?:(?:xn--)?[a-z0-9]+(?:-[a-z0-9]+)*\.){1,126}){1,}(?:(?:[a-z][a-z0-9]*)|(?:(?:xn--)[a-z0-9]+))(?:-[a-z0-9]+)*)|(?:\[(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){7})|(?:(?!(?:.*[a-f0-9][:\]]){7,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,5})?)))|(?:(?:IPv6:(?:(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){5}:)|(?:(?!(?:.*[a-f0-9]:){5,})(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3})?::(?:[a-f0-9]{1,4}(?::[a-f0-9]{1,4}){0,3}:)?)))?(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))(?:\.(?:(?:25[0-5])|(?:2[0-4][0-9])|(?:1[0-9]{2})|(?:[1-9]?[0-9]))){3}))\]))$/iD',
                $value);
            // @codingStandardsIgnoreEnd
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
