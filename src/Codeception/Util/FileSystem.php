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
            return @unlink($dir);
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {            
            $dir = str_replace('/', '\\', $dir);
            exec('rd /s /q "'.$dir.'"');
            return true;
        }

        foreach (scandir($dir) as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }

            if (!self::deleteDir($dir . DIRECTORY_SEPARATOR . $item)) {
                chmod($dir . DIRECTORY_SEPARATOR . $item, 0777);
                if (!self::deleteDir($dir . DIRECTORY_SEPARATOR . $item)) {
                    return false;
                }
            }
        }

        return @rmdir($dir);
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
                if (is_dir($src . DIRECTORY_SEPARATOR . $file)) {
                    self::copyDir($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                } else {
                    copy($src . DIRECTORY_SEPARATOR . $file, $dst . DIRECTORY_SEPARATOR . $file);
                }
            }
        }
        closedir($dir);
    }
}
