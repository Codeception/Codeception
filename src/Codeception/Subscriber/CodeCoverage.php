<?php
namespace Codeception\Subscriber;

use Codeception\CodeceptionEvents;
use Codeception\CodeCoverageSettings;
use Codeception\Configuration;
use Codeception\Event\PrintResultEvent;
use Codeception\Event\SuiteEvent;
use Codeception\Exception\RemoteException;
use Codeception\PHPUnit\DummyCodeCoverage;
use Codeception\SuiteManager;
use Codeception\Util\RemoteInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Retrieves CodeCoverage data from remote server
 */
class CodeCoverage implements EventSubscriberInterface
{
    /**
     * @var \Codeception\Util\RemoteInterface
     */
    protected $coverage = null;
    protected $options = array();
    protected $log_dir = null;

    protected $enabled = null;
    protected $remote = null;
    protected $module = null;

    // defaults
    protected $settings = array(
        'enabled'        => false,
        'remote'         => false,
        'low_limit'      => '35',
        'high_limit'     => '70',
        'show_uncovered' => false
    );

    protected $http = array(
        'method' => 'GET',
        'header' => ''
    );

    function __construct($options = array())
    {
        $this->options  = $options;
        $this->coverage = new \PHP_CodeCoverage();
        $this->log_dir  = Configuration::logDir();
    }

    /**
     * @return RemoteInterface|null
     */
    protected function getRemoteConnectionModule()
    {
        foreach (SuiteManager::$modules as $module) {
            if ($module instanceof RemoteInterface) {
                return $module;
            }
        }
        return null;
    }

    public function beforeSuite(SuiteEvent $e)
    {
        $settings = $e->getSettings();
        $this->applySettings($settings);

        $e->getResult()->setCodeCoverage(new DummyCodeCoverage);

        if (! $this->enabled or $this->remote) {
            return;
        }

        CodeCoverageSettings::setup($this->coverage)
                            ->filterWhiteList($settings)
                            ->filterBlackList($settings);

        $e->getResult()->setCodeCoverage($this->coverage);
    }

    /**
     * merge local code coverages
     * skip code coverage on remote server
     * fetch and merge
     *
     * @param SuiteEvent $e
     */
    public function afterSuite(SuiteEvent $e)
    {
        if (! $this->enabled) {
            return;
        }

        $coverage = $e->getResult()->getCodeCoverage();

        if (! $this->remote) {
            $this->coverage->merge($coverage);
            return;
        }

        $remoteModule = $this->getRemoteConnectionModule();
        if (! ($remoteModule instanceof RemoteInterface)) {
            $this->coverage->merge($coverage);
            return;
        };

        $externalCoverage = $this->getRemoteCoverageFile($remoteModule, 'serialized');
        if (! $externalCoverage) {
            return;
        }

        $coverage = @unserialize($externalCoverage);
        if ($coverage === false) {
            return;
        }

        $this->coverage->merge($coverage);
    }

    /**
     * @param RemoteInterface $module
     * @param                 $type
     *
     * @return bool|string
     */
    protected function getRemoteCoverageFile($module, $type)
    {
        $this->addHeader('X-Codeception-CodeCoverage', 'remote-access');
        $context  = stream_context_create(array('http' => $this->http));
        $contents = file_get_contents($module->_getUrl() . '/c3/report/' . $type, false, $context);
        if ($contents === false) {
            $this->getRemoteError($http_response_header);
        }
        return $contents;
    }

    protected function getRemoteError($headers)
    {
        foreach ($headers as $header) {
            if (strpos($header, 'X-Codeception-CodeCoverage-Error') === 0) {
                throw new RemoteException($header);
            }
        }
    }

    protected function addHeader($header, $value)
    {
        $this->http['header'] .= "$header: $value\r\n";
    }

    public function printResult(PrintResultEvent $e)
    {
        if ($this->options['html']) {
            $this->printHtml();
        }
        if ($this->options['xml']) {
            $this->printXml();
        }
        if ($this->options['steps']) {
            return;
        }
        $this->printText($e->getPrinter());
        $this->printPHP();
    }

    protected function printText(\PHPUnit_Util_Printer $printer)
    {
        $writer = new \PHP_CodeCoverage_Report_Text(
            $printer,
            $this->settings['low_limit'],
            $this->settings['high_limit'],
            $this->settings['show_uncovered']
        );
        $printer->write($writer->process($this->coverage, $this->options['colors']));
    }

    protected function printHtml()
    {
        $writer = new \PHP_CodeCoverage_Report_HTML(
            'UTF-8',
            true,
            $this->settings['low_limit'],
            $this->settings['high_limit'],
            sprintf(
                ', <a href="http://codeception.com">Codeception</a> and <a href="http://phpunit.de/">PHPUnit %s</a>',
                \PHPUnit_Runner_Version::id()
            )
        );

        $writer->process($this->coverage, $this->log_dir . 'coverage');
    }

    protected function printXml()
    {
        $writer = new \PHP_CodeCoverage_Report_Clover;
        $writer->process($this->coverage, $this->log_dir . 'coverage.xml');
    }

    protected function printPHP()
    {
        $writer = new \PHP_CodeCoverage_Report_PHP;
        $writer->process($this->coverage, Configuration::logDir() . 'coverage.serialized');
    }

    protected function applySettings($settings)
    {
        if (! function_exists('xdebug_is_enabled')) {
            throw new \Exception('XDebug is required to collect CodeCoverage. Please install xdebug extension and enable it in php.ini');
        }

        $keys = array_keys($this->settings);
        foreach ($keys as $key) {
            if (isset($settings['coverage'][$key])) {
                $this->settings[$key] = $settings['coverage'][$key];
            }
        }
        $this->enabled = $this->settings['enabled'];
        $this->remote  = $this->settings['remote'];
    }

    static function getSubscribedEvents()
    {
        return array(
            CodeceptionEvents::SUITE_BEFORE       => 'beforeSuite',
            CodeceptionEvents::SUITE_AFTER        => 'afterSuite',
            CodeceptionEvents::RESULT_PRINT_AFTER => 'printResult'
        );
    }
}
