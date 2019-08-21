<?php

namespace Codeception\Util;

use Codeception\Exception\ConfigurationException;

class PathResolver
{
    /**
     * Returns path to a given directory relative to $projDir.
     * @param string $path
     * @param string $projDir
     * @param string $dirSep
     * @return string
     */
    public static function getRelativeDir($path, $projDir, $dirSep = DIRECTORY_SEPARATOR)
    {
        // ensure $projDir ends with a trailing $dirSep
        $projDir = preg_replace('/'.preg_quote($dirSep, '/').'*$/', $dirSep, $projDir);
        // if $path is a within $projDir
        if (self::fsCaseStrCmp(substr($path, 0, strlen($projDir)), $projDir, $dirSep) == 0) {
            // simply chop it off the front
            return substr($path, strlen($projDir));
        }
        // Identify any absoluteness prefix (like '/' in Unix or "C:\\" in Windows)
        $pathAbsPrefix = self::getPathAbsolutenessPrefix($path, $dirSep);
        $projDirAbsPrefix = self::getPathAbsolutenessPrefix($projDir, $dirSep);
        $sameAbsoluteness = (self::fsCaseStrCmp($pathAbsPrefix['wholePrefix'], $projDirAbsPrefix['wholePrefix'], $dirSep) == 0);
        if (!$sameAbsoluteness) {
            // if the $projDir and $path aren't relative to the same
            // thing, we can't make a relative path.

            // if we're relative to the same device ...
            if (strlen($pathAbsPrefix['devicePrefix']) &&
                (self::fsCaseStrCmp($pathAbsPrefix['devicePrefix'], $projDirAbsPrefix['devicePrefix'], $dirSep) == 0)
            ) {
                // ... shave that off
                return substr($path, strlen($pathAbsPrefix['devicePrefix']));
            }
            // Return the input unaltered
            return $path;
        }
        // peel off optional absoluteness prefixes and convert
        // $path and $projDir to an subdirectory path array
        $relPathParts = array_filter(explode($dirSep, substr($path, strlen($pathAbsPrefix['wholePrefix']))), 'strlen');
        $relProjDirParts = array_filter(explode($dirSep, substr($projDir, strlen($projDirAbsPrefix['wholePrefix']))), 'strlen');
        // While there are any, peel off any common parent directories
        // from the beginning of the $projDir and $path
        while ((count($relPathParts) > 0) && (count($relProjDirParts) > 0) &&
            (self::fsCaseStrCmp($relPathParts[0], $relProjDirParts[0], $dirSep) == 0)
        ) {
            array_shift($relPathParts);
            array_shift($relProjDirParts);
        }
        if (count($relProjDirParts) > 0) {
            // prefix $relPath with '..' for all remaining unmatched $projDir
            // subdirectories
            $relPathParts = array_merge(array_fill(0, count($relProjDirParts), '..'), $relPathParts);
        }
        // only append a trailing seperator if one is already present
        $trailingSep = preg_match('/'.preg_quote($dirSep, '/').'$/', $path) ? $dirSep : '';
        // convert array of dir paths back into a string path
        return implode($dirSep, $relPathParts).$trailingSep;
    }
    /**
     * FileSystem Case String Compare
     * compare two strings with the filesystem's case-sensitiveness
     *
     * @param string $str1
     * @param string $str2
     * @param string $dirSep
     * @return int -1 / 0 / 1 for < / = / > respectively
     */
    private static function fsCaseStrCmp($str1, $str2, $dirSep = DIRECTORY_SEPARATOR)
    {
        $cmpFn = self::isWindows($dirSep) ? 'strcasecmp' : 'strcmp';
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
     * @param string $path
     * @param string $dirSep
     * @return string
     */
    private static function getPathAbsolutenessPrefix($path, $dirSep = DIRECTORY_SEPARATOR)
    {
        $devLetterPrefixPattern = '';
        if (self::isWindows($dirSep)) {
            $devLetterPrefixPattern = '([A-Za-z]:|)';
        }
        $matches = [];
        if (!preg_match('/^'.$devLetterPrefixPattern.preg_quote($dirSep, '/').'?/', $path, $matches)) {
            // This should match, even if it matches 0 characters
            throw new ConfigurationException("INTERNAL ERROR: This must be a regex problem.");
        }
        return [
            'wholePrefix'  => $matches[0], // The optional device letter followed by the optional $dirSep
            'devicePrefix' => self::isWindows($dirSep) ? $matches[1] : ''];
    }

    /**
     * Are we in a Windows style filesystem?
     *
     * @param string $dirSep
     * @return bool
     */
    private static function isWindows($dirSep = DIRECTORY_SEPARATOR)
    {
        return ($dirSep == '\\');
    }

    public static function isPathAbsolute($path)
    {
        if (DIRECTORY_SEPARATOR === '/') {
            return substr($path, 0, 1) === DIRECTORY_SEPARATOR;
        }

        return preg_match('#^[A-Z]:(?![^/\\\])#i', $path) === 1;
    }
}
