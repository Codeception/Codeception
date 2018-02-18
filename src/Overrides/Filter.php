<?php

// @codingStandardsIgnoreStart
class PHPUnit_Util_Filter
{
    // @codingStandardsIgnoreEnd
    protected static $filteredClassesPattern = [
        'Symfony\Component\Console',
        'Codeception\Command\\',
        'Codeception\TestCase\\',
    ];

    public static function getFilteredStackTrace($e, $asString = true, $filter = true)
    {
        $stackTrace = $asString ? '' : [];

        $trace = $e->getPrevious() ? $e->getPrevious()->getTrace() : $e->getTrace();
        if ($e instanceof \PHPUnit_Framework_ExceptionWrapper) {
            $trace = $e->getSerializableTrace();
        }

        $eFile = $e->getFile();
        $eLine = $e->getLine();

        if (!self::frameExists($trace, $eFile, $eLine)) {
            array_unshift(
                $trace,
                ['file' => $eFile, 'line' => $eLine]
            );
        }

        foreach ($trace as $step) {
            if (self::classIsFiltered($step) and $filter) {
                continue;
            }
            if (self::fileIsFiltered($step) and $filter) {
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

        foreach (self::$filteredClassesPattern as $filteredClassName) {
            if (strpos($className, $filteredClassName) === 0) {
                return true;
            }
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

        $modulePath = 'src' . DIRECTORY_SEPARATOR . 'Codeception' . DIRECTORY_SEPARATOR . 'Module';
        if (strpos($step['file'], $modulePath) !== false) {
            return false; // don`t filter modules
        }

        if (strpos($step['file'], 'src' . DIRECTORY_SEPARATOR . 'Codeception' . DIRECTORY_SEPARATOR) !== false) {
            return true;
        }

        return false;
    }

    /**
     * @param array  $trace
     * @param string $file
     * @param int    $line
     *
     * @return bool
     */
    private static function frameExists(array $trace, $file, $line)
    {
        foreach ($trace as $frame) {
            if (isset($frame['file']) && $frame['file'] == $file &&
                isset($frame['line']) && $frame['line'] == $line) {
                return true;
            }
        }

        return false;
    }
}
