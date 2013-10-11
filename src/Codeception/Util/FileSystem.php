<?php

namespace Codeception\Util;

/**
 * @author tiger
 */
class FileSystem
{
    public static function doEmptyDir($path)
    {
        $iterator = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($path),
            \RecursiveIteratorIterator::CHILD_FIRST
        );

        foreach ($iterator as $path) {
            if ($path->isDir()) {
                $dir = (string) $path;
                if (basename($dir) === '.' || basename($dir) === '..') {
                    continue;
                }
                rmdir($dir);
            } else {
                $file = (string)$path;
                if (basename($file) === '.gitignore') {
                    continue;
                }
                unlink($path->__toString());
            }
        }
    }

    public static function deleteDir($dir)
    {
        if (!file_exists($dir)) return true;
        if (!is_dir($dir) || is_link($dir)) return unlink($dir);
        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') continue;
            if (!self::deleteDir($dir . "/" . $item)) {
                chmod($dir . "/" . $item, 0777);
                if (!self::deleteDir($dir . "/" . $item)) return false;
            }
        }
        return rmdir($dir);
    }

    public static function copyDir($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    self::copyDir($src . '/' . $file,$dst . '/' . $file);
                }
                else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
