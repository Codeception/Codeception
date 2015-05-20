<?php

namespace Codeception\Util;

/**
 * Set of functions to work with Filesystem
 *
 */
class FileSystem
{
    /**
     * @param $path
     */
    public static function doEmptyDir($path)
    {
        /** @var $iterator \RecursiveIteratorIterator|\SplFileObject[] */
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $path) {
            $basename = basename((string)$path);
            if ($basename === '.' || $basename === '..' || $basename === '.gitignore') {
                continue;
            }

            if ($path->isDir()) {
                rmdir((string)$path);
            } else {
                unlink((string)$path);
            }
        }
    }

    /**
     * @param $dir
     * @return bool
     */
    public static function deleteDir($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir) || is_link($dir)) {
            return unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (!self::deleteDir($dir . '/' . $item)) {
                chmod($dir . '/' . $item, 0777);
                if (!self::deleteDir($dir . '/' . $item)) {
                    return false;
                }
            }
        }

        return rmdir($dir);
    }

    /**
     * @param $src
     * @param $dst
     */
    public static function copyDir($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::copyDir($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
