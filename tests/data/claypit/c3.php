<?php
// @codingStandardsIgnoreFile
// @codeCoverageIgnoreStart

/**
 * C3 - Codeception Code Coverage
 *
 * @author tiger
 */

// $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_DEBUG'] = 1;

if (isset($_COOKIE['CODECEPTION_CODECOVERAGE'])) {
    $cookie = json_decode($_COOKIE['CODECEPTION_CODECOVERAGE'], true);

    // fix for improperly encoded JSON in Code Coverage cookie with WebDriver.
    // @see https://github.com/Codeception/Codeception/issues/874
    if (!is_array($cookie)) {
        $cookie = json_decode($cookie, true);
    }

    if ($cookie) {    
        foreach ($cookie as $key => $value) {
            $_SERVER["HTTP_X_CODECEPTION_" . strtoupper($key)] = $value;
        }
    }
}

if (!array_key_exists('HTTP_X_CODECEPTION_CODECOVERAGE', $_SERVER)) {
    return;
}

if (!function_exists('__c3_error')) {
    function __c3_error($message)
    {
        $errorLogFile = defined('C3_CODECOVERAGE_ERROR_LOG_FILE') ?
            C3_CODECOVERAGE_ERROR_LOG_FILE :
            C3_CODECOVERAGE_MEDIATE_STORAGE . DIRECTORY_SEPARATOR . 'error.txt';
        if (is_writable($errorLogFile)) {
            file_put_contents($errorLogFile, $message);
        } else {
            $message = "Could not write error to log file ($errorLogFile), original message: $message";
        }
        if (!headers_sent()) {
            header('X-Codeception-CodeCoverage-Error: ' . str_replace("\n", ' ', $message), true, 500);
        }
        setcookie('CODECEPTION_CODECOVERAGE_ERROR', $message);
    }
}

// phpunit codecoverage shimming
if (class_exists('SebastianBergmann\CodeCoverage\CodeCoverage') and !class_exists('PHP_CodeCoverage')) {
      class_alias('SebastianBergmann\CodeCoverage\CodeCoverage', 'PHP_CodeCoverage');
      class_alias('SebastianBergmann\CodeCoverage\Report\Text', 'PHP_CodeCoverage_Report_Text');
      class_alias('SebastianBergmann\CodeCoverage\Report\PHP', 'PHP_CodeCoverage_Report_PHP');
      class_alias('SebastianBergmann\CodeCoverage\Report\Clover', 'PHP_CodeCoverage_Report_Clover');
      class_alias('SebastianBergmann\CodeCoverage\Report\Crap4j', 'PHP_CodeCoverage_Report_Crap4j');
      class_alias('SebastianBergmann\CodeCoverage\Report\Html\Facade', 'PHP_CodeCoverage_Report_HTML');
      class_alias('SebastianBergmann\CodeCoverage\Report\Xml\Facade', 'PHP_CodeCoverage_Report_XML');
      class_alias('SebastianBergmann\CodeCoverage\Exception', 'PHP_CodeCoverage_Exception');
}

// Autoload Codeception classes
if (!class_exists('\\Codeception\\Codecept')) {
    if (file_exists(__DIR__ . '/codecept.phar')) {
        require_once 'phar://' . __DIR__ . '/codecept.phar/autoload.php';
    } elseif (stream_resolve_include_path(__DIR__ . '/vendor/autoload.php')) {
        require_once __DIR__ . '/vendor/autoload.php';
        // Required to load some methods only available at codeception/autoload.php
        if (stream_resolve_include_path(__DIR__ . '/vendor/codeception/codeception/autoload.php')) {
            require_once __DIR__ . '/vendor/codeception/codeception/autoload.php';
        }
    } elseif (stream_resolve_include_path('Codeception/autoload.php')) {
        require_once 'Codeception/autoload.php';
    } else {
        __c3_error('Codeception is not loaded. Please check that either PHAR or Composer package can be used');
    }
}

// Load Codeception Config
$config_dist_file = realpath(__DIR__) . DIRECTORY_SEPARATOR . 'codeception.dist.yml';
$config_file = realpath(__DIR__) . DIRECTORY_SEPARATOR . 'codeception.yml';

if (isset($_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_CONFIG'])) {
    $config_file = realpath(__DIR__) . DIRECTORY_SEPARATOR . $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_CONFIG'];
}
if (file_exists($config_file)) {
    // Use codeception.yml for configuration.
} elseif (file_exists($config_dist_file)) {
    // Use codeception.dist.yml for configuration.
    $config_file = $config_dist_file;
} else {
    __c3_error(sprintf("Codeception config file '%s' not found", $config_file));
}
try {
    \Codeception\Configuration::config($config_file);
} catch (\Exception $e) {
    __c3_error($e->getMessage());
}

if (!defined('C3_CODECOVERAGE_MEDIATE_STORAGE')) {

    // workaround for 'zend_mm_heap corrupted' problem
    gc_disable();

    $memoryLimit = ini_get('memory_limit');
    $requiredMemory = '384M';
    if ((substr($memoryLimit, -1) === 'M' && (int)$memoryLimit < (int)$requiredMemory)
        || (substr($memoryLimit, -1) === 'K' && (int)$memoryLimit < (int)$requiredMemory * 1024)
        || (ctype_digit($memoryLimit) && (int)$memoryLimit < (int)$requiredMemory * 1024 * 1024)
    ) {
        ini_set('memory_limit', $requiredMemory);
    }

    define('C3_CODECOVERAGE_MEDIATE_STORAGE', Codeception\Configuration::logDir() . 'c3tmp');
    define('C3_CODECOVERAGE_PROJECT_ROOT', Codeception\Configuration::projectDir());
    define('C3_CODECOVERAGE_TESTNAME', $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE']);

    function __c3_build_html_report(PHP_CodeCoverage $codeCoverage, $path)
    {
        $writer = new PHP_CodeCoverage_Report_HTML();
        $writer->process($codeCoverage, $path . 'html');

        if (file_exists($path . '.tar')) {
            unlink($path . '.tar');
        }

        $phar = new PharData($path . '.tar');
        $phar->setSignatureAlgorithm(Phar::SHA1);
        $files = $phar->buildFromDirectory($path . 'html');
        array_map('unlink', $files);

        if (in_array('GZ', Phar::getSupportedCompression())) {
            if (file_exists($path . '.tar.gz')) {
                unlink($path . '.tar.gz');
            }

            $phar->compress(\Phar::GZ);

            // close the file so that we can rename it
            unset($phar);

            unlink($path . '.tar');
            rename($path . '.tar.gz', $path . '.tar');
        }

        return $path . '.tar';
    }

    function __c3_build_clover_report(PHP_CodeCoverage $codeCoverage, $path)
    {
        $writer = new PHP_CodeCoverage_Report_Clover();
        $writer->process($codeCoverage, $path . '.clover.xml');

        return $path . '.clover.xml';
    }

    function __c3_build_crap4j_report(PHP_CodeCoverage $codeCoverage, $path)
    {
        $writer = new PHP_CodeCoverage_Report_Crap4j();
        $writer->process($codeCoverage, $path . '.crap4j.xml');

        return $path . '.crap4j.xml';
    }

    function __c3_build_phpunit_report(PHP_CodeCoverage $codeCoverage, $path)
    {
        $writer = new PHP_CodeCoverage_Report_XML(\PHPUnit_Runner_Version::id());
        $writer->process($codeCoverage, $path . 'phpunit');

        if (file_exists($path . '.tar')) {
            unlink($path . '.tar');
        }

        $phar = new PharData($path . '.tar');
        $phar->setSignatureAlgorithm(Phar::SHA1);
        $files = $phar->buildFromDirectory($path . 'phpunit');
        array_map('unlink', $files);

        if (in_array('GZ', Phar::getSupportedCompression())) {
            if (file_exists($path . '.tar.gz')) {
                unlink($path . '.tar.gz');
            }

            $phar->compress(\Phar::GZ);

            // close the file so that we can rename it
            unset($phar);

            unlink($path . '.tar');
            rename($path . '.tar.gz', $path . '.tar');
        }

        return $path . '.tar';
    }

    function __c3_send_file($filename)
    {
        if (!headers_sent()) {
            readfile($filename);
        }

        return __c3_exit();
    }

    /**
     * @param $filename
     * @return null|PHP_CodeCoverage
     */
    function __c3_factory($filename)
    {
        $phpCoverage = is_readable($filename)
            ? unserialize(file_get_contents($filename))
            : new PHP_CodeCoverage();


        if (isset($_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_SUITE'])) {
            $suite = $_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_SUITE'];
            try {
                $settings = \Codeception\Configuration::suiteSettings($suite, \Codeception\Configuration::config());
            } catch (Exception $e) {
                __c3_error($e->getMessage());
            }
        } else {
            $settings = \Codeception\Configuration::config();
        }

        try {
            \Codeception\Coverage\Filter::setup($phpCoverage)
                ->whiteList($settings)
                ->blackList($settings);
        } catch (Exception $e) {
            __c3_error($e->getMessage());
        }

        return $phpCoverage;
    }

    function __c3_exit()
    {
        if (!isset($_SERVER['HTTP_X_CODECEPTION_CODECOVERAGE_DEBUG'])) {
            exit;
        }
        return null;
    }

    function __c3_clear()
    {
        \Codeception\Util\FileSystem::doEmptyDir(C3_CODECOVERAGE_MEDIATE_STORAGE);
    }
}

if (!is_dir(C3_CODECOVERAGE_MEDIATE_STORAGE)) {
    if (mkdir(C3_CODECOVERAGE_MEDIATE_STORAGE, 0777, true) === false) {
        __c3_error('Failed to create directory "' . C3_CODECOVERAGE_MEDIATE_STORAGE . '"');
    }
}

// evaluate base path for c3-related files
$path = realpath(C3_CODECOVERAGE_MEDIATE_STORAGE) . DIRECTORY_SEPARATOR . 'codecoverage';

$requested_c3_report = (strpos($_SERVER['REQUEST_URI'], 'c3/report') !== false);

$complete_report = $current_report = $path . '.serialized';
if ($requested_c3_report) {
    set_time_limit(0);

    $route = ltrim(strrchr($_SERVER['REQUEST_URI'], '/'), '/');

    if ($route == 'clear') {
        __c3_clear();
        return __c3_exit();
    }

    $codeCoverage = __c3_factory($complete_report);

    switch ($route) {
        case 'html':
            try {
                __c3_send_file(__c3_build_html_report($codeCoverage, $path));
            } catch (Exception $e) {
                __c3_error($e->getMessage());
            }
            return __c3_exit();
        case 'clover':
            try {
                __c3_send_file(__c3_build_clover_report($codeCoverage, $path));
            } catch (Exception $e) {
                __c3_error($e->getMessage());
            }
            return __c3_exit();
        case 'crap4j':
            try {
                __c3_send_file(__c3_build_crap4j_report($codeCoverage, $path));
            } catch (Exception $e) {
                __c3_error($e->getMessage());
            }
            return __c3_exit();
        case 'serialized':
            try {
                __c3_send_file($complete_report);
            } catch (Exception $e) {
                __c3_error($e->getMessage());
            }
            return __c3_exit();
        case 'phpunit':
            try {
                __c3_send_file(__c3_build_phpunit_report($codeCoverage, $path));
            } catch (Exception $e) {
                __c3_error($e->getMessage());
            }
            return __c3_exit();
    }

} else {
    $codeCoverage = __c3_factory($current_report);
    $codeCoverage->start(C3_CODECOVERAGE_TESTNAME);
    if (!array_key_exists('HTTP_X_CODECEPTION_CODECOVERAGE_DEBUG', $_SERVER)) { 
        register_shutdown_function(
            function () use ($codeCoverage, $current_report) {

                $codeCoverage->stop();
                if (!file_exists(dirname($current_report))) { // verify directory exists
                    if (!mkdir(dirname($current_report), 0777, true)) {
                        __c3_error("Can't write CodeCoverage report into $current_report");
                    }
                }

                file_put_contents($current_report, serialize($codeCoverage));
            }
        );
    }
}

// @codeCoverageIgnoreEnd
