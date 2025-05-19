<?php

declare(strict_types=1);

namespace Codeception\Util;

use function array_fill;
use function array_filter;
use function array_merge;
use function array_shift;
use function count;
use function explode;
use function implode;
use function preg_match;
use function strlen;
use function substr;

class PathResolver
{
    /**
     * Returns path to a given directory relative to $projDir.
     */
    public static function getRelativeDir(string $path, string $projDir, string $dirSep = DIRECTORY_SEPARATOR): string
    {
        $projDir = rtrim($projDir, $dirSep) . $dirSep;
        $projLen = strlen($projDir);

        if (self::fsCaseStrCmp(substr($path, 0, $projLen), $projDir, $dirSep) === 0) {
            return substr($path, $projLen);
        }

        $pathPref = self::getPathAbsolutenessPrefix($path, $dirSep);
        $projPref = self::getPathAbsolutenessPrefix($projDir, $dirSep);

        if (self::fsCaseStrCmp($pathPref['wholePrefix'], $projPref['wholePrefix'], $dirSep) !== 0) {
            if ($pathPref['devicePrefix'] !== '' && self::fsCaseStrCmp($pathPref['devicePrefix'], $projPref['devicePrefix'], $dirSep) === 0) {
                return substr($path, strlen($pathPref['devicePrefix']));
            }
            return $path;
        }

        $baseLen   = strlen($pathPref['wholePrefix']);
        $partsPath = array_values(array_filter(explode($dirSep, substr($path, $baseLen))));
        $partsProj = array_values(array_filter(explode($dirSep, substr($projDir, strlen($projPref['wholePrefix'])))));

        while ($partsPath && $partsProj && self::fsCaseStrCmp($partsPath[0], $partsProj[0], $dirSep) === 0) {
            array_shift($partsPath);
            array_shift($partsProj);
        }

        if ($partsProj !== []) {
            $partsPath = array_merge(array_fill(0, count($partsProj), '..'), $partsPath);
        }

        $trailingSep = (substr($path, -1) === $dirSep) ? $dirSep : '';

        return implode($dirSep, $partsPath) . $trailingSep;
    }

    /**
     * FileSystem Case String Comparison
     * Compare two strings with the filesystem's case-sensitiveness
     *
     * @return int -1 / 0 / 1 for < / = / > respectively
     */
    private static function fsCaseStrCmp(string $str1, string $str2, string $dirSep = DIRECTORY_SEPARATOR): int
    {
        $cmpFn = self::isWindowsFilesystem($dirSep) ? 'strcasecmp' : 'strcmp';
        return $cmpFn($str1, $str2);
    }

    /**
     * What part of this path (leftmost 0-3 characters) what
     * it is absolute relative to:
     *
     * On Unix:
     *     This is simply '/' for an absolute path or
     *     '' for a relative path
     *
     * On Windows this is more complicated:
     *     If the first two characters are a letter followed
     *         by a ':', this indicates that the path is
     *         on a specific device.
     *     With or without a device specified, a path MAY
     *         start with a '\\' to indicate an absolute path
     *         on the device or '' to indicate a path relative
     *         to the device's CWD
     *
     * @return array<string, string>
     */
    private static function getPathAbsolutenessPrefix(string $path, string $dirSep = DIRECTORY_SEPARATOR): array
    {
        $isWindows = self::isWindowsFilesystem($dirSep);

        if ($isWindows && preg_match('/^[A-Za-z]:/', $path, $m)) {
            $dev = $m[0];
            $hasDirSep = (substr($path, strlen($dev), 1) === $dirSep) ? $dirSep : '';
            return [
                'wholePrefix'  => $dev . $hasDirSep,
                'devicePrefix' => $dev,
            ];
        }
        $wholePrefix = ($path !== '' && $path[0] === $dirSep) ? $dirSep : '';

        return [
            'wholePrefix'  => $wholePrefix,
            'devicePrefix' => '',
        ];
    }

    private static function isWindowsFilesystem(string $dirSep = DIRECTORY_SEPARATOR): bool
    {
        return $dirSep === '\\';
    }

    public static function isPathAbsolute(string $path): bool
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return $path !== '' && $path[0] === DIRECTORY_SEPARATOR;
        }

        return preg_match('#^[A-Z]:(?![^/\\\\])#i', $path) === 1;
    }
}
