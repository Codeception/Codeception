<?php

namespace Codeception\Util;

/**
 * Multibyte handling methods
 */
class Multibyte
{

    /**
     * strlen
     *
     * @param string $string
     * @param string $encoding
     * @return string
     */
    public static function strlen($string, $encoding = 'UTF-8')
    {
        if (!function_exists('mb_strlen')) {
            return strlen($string);
        }
        return mb_strlen($string, $encoding);
    }

    /**
     * strwidth
     *
     * @param string $string
     * @param string $encoding
     * @return string
     */
    public static function strwidth($string, $encoding = 'UTF-8')
    {
        if (!function_exists('mb_strwidth')) {
            return strlen($string);
        }
        return mb_strwidth($string, $encoding);
    }

    /**
     * substr
     *
     * @param string  $string
     * @param integer $start
     * @param integer $length
     * @param string  $encoding
     * @return string
     */
    public static function substr($string, $start, $length = null, $encoding = 'UTF-8')
    {
        if (!function_exists('mb_substr')) {
            $length = is_null($length) ? strlen($length) : $length;
            return substr($string, $start, $length);
        }
        return mb_substr($string, $start, $length, $encoding);
    }

    /**
     * strtolower
     *
     * @param string $string
     * @param string $encoding
     * @return string
     */
    public static function strtolower($string, $encoding = 'UTF-8')
    {
        if (!function_exists('mb_strtolower')) {
            return strtolower($string);
        }
        return mb_strtolower($string, $encoding);
    }

    /**
     * strtoupper
     *
     * @param string $string
     * @param string $encoding
     * @return string
     */
    public static function strtoupper($string, $encoding = 'UTF-8')
    {
        if (!function_exists('mb_strtoupper')) {
            return strtoupper($string);
        }
        return mb_strtoupper($string, $encoding);
    }

    /**
     * ucfirst
     *
     * @param string $string
     * @param string $encoding
     * @return string
     */
    public static function ucfirst($string, $encoding = 'UTF-8')
    {
        if (!function_exists('mb_strtoupper')) {
            return ucfirst($string);
        }
        $first = mb_substr($string, 0, 1, $encoding);
        $after = mb_substr($string, 1, null, $encoding);
        return mb_strtoupper($first, $encoding) . $after;
    }
}
