<?php

class PHPUnit_Util_Filter
{
    public static function getFilteredStackTrace(Exception $e, $asString = true)
    {
        $stackTrace = $asString ? '' : [];

        $trace = $e->getPrevious() ? $e->getPrevious()->getTrace() : $e->getTrace();
        if ($e instanceof \PHPUnit_Framework_ExceptionWrapper) {
            $trace = $e->getSerializableTrace();
        }

        foreach ($trace as $step) {

            if (self::classIsFiltered($step)) {
                continue;
            }
            if (self::fileIsFiltered($step)) {
                continue;
            }

            if (!$asString) {
                $stackTrace[] = $step;
                continue;
            }
            if (!isset($step['file'])) {
                continue;
            }

            $stackTrace .= $step['file'] . ':' . $step['line'] . "\n";
        }

        return $stackTrace;
    }

    protected static function classIsFiltered($step)
    {
        if (!isset($step['class'])) {
            return false;
        }

        $className = $step['class'];

        if (strpos($className, 'Symfony\Component\Console') === 0) {
            return true;
        }

        if (strpos($className, 'Codeception\Command\\') === 0) {
            return true;
        }

        if (strpos($className, 'Codeception\TestCase\\') === 0) {
            return true;
        }

        return false;
    }

    protected static function fileIsFiltered($step)
    {
        if (!isset($step['file'])) {
            return false;
        }
        if (strpos($step['file'], 'codecept.phar/') !== false) {
            return true;
        }

        if (strpos($step['file'], 'vendor' . DIRECTORY_SEPARATOR . 'phpunit') !== false) {
            return true;
        }

        if (strpos($step['file'], 'vendor' . DIRECTORY_SEPARATOR . 'codeception') !== false) {
            return true;
        }

        if (strpos($step['file'], 'src' . DIRECTORY_SEPARATOR . 'Codeception' . DIRECTORY_SEPARATOR . 'Module') !== false) {
            return false; // don`t filter modules
        }

        if (strpos($step['file'], 'src' . DIRECTORY_SEPARATOR . 'Codeception' . DIRECTORY_SEPARATOR) !== false) {
            return true;
        }

        return false;
    }
}
