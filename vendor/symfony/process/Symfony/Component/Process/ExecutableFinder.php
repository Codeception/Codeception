<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Process;

/**
 * Generic executable finder.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 */
class ExecutableFinder
{
    private static $isWindows;

    private $suffixes = array('.exe', '.bat', '.cmd', '.com');

    public function __construct()
    {
        if (null === self::$isWindows) {
            self::$isWindows = 0 === stripos(PHP_OS, 'win');
        }
    }

    public function setSuffixes(array $suffixes)
    {
        $this->suffixes = $suffixes;
    }

    public function addSuffix($suffix)
    {
        $this->suffixes[] = $suffix;
    }

    /**
     * Finds an executable by name.
     *
     * @param string $name      The executable name (without the extension)
     * @param string $default   The default to return if no executable is found
     * @param array  $extraDirs Additional dirs to check into
     *
     * @return string The executable path or default value
     */
    public function find($name, $default = null, array $extraDirs = array())
    {
        if (ini_get('open_basedir')) {
            $searchPath = explode(PATH_SEPARATOR, getenv('open_basedir'));
            $dirs = array();
            foreach ($searchPath as $path) {
                if (is_dir($path)) {
                    $dirs[] = $path;
                } else {
                    $file = str_replace(dirname($path), '', $path);
                    if ($file == $name && is_executable($path)) {
                        return $path;
                    }
                }
            }
        } else {
            $dirs = array_merge(
                explode(PATH_SEPARATOR, getenv('PATH') ?: getenv('Path')),
                $extraDirs
            );
        }

        $suffixes = array('');
        if (defined('PHP_WINDOWS_VERSION_BUILD')) {
            $pathExt = getenv('PATHEXT');
            $suffixes = $pathExt ? explode(PATH_SEPARATOR, $pathExt) : $this->suffixes;
        }
        foreach ($suffixes as $suffix) {
            foreach ($dirs as $dir) {
                if (is_file($file = $dir.DIRECTORY_SEPARATOR.$name.$suffix) && (self::$isWindows || is_executable($file))) {
                    return $file;
                }
            }
        }

        return $default;
    }
}
