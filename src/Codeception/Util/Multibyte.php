<?php

namespace Codeception\Util;

/**
 * Multibyte handling methods
 */
class Multibyte
{

    /**
     * ucfirst
     *
     * @param string $string
     * @param string $encoding
     * @return string
     */
    public static function ucfirst($string, $encoding = 'UTF-8')
    {
        $first = mb_substr($string, 0, 1, $encoding);
        $after = mb_substr($string, 1, null, $encoding);
        return mb_strtoupper($first, $encoding) . $after;
    }
}
