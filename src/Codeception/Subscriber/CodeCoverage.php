<?php
namespace Codeception\Subscriber;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Codeception\Configuration;

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

    protected $enabled = null;
    protected $remote = null;
    protected $module = null;

    // defaults
    protected $settings = array('enabled' => false, 'remote' => false, 'low_limit' => '35', 'high_limit' => '70', 'show_uncovered' => false);


    function __construct($options = array())
    {
        $this->options = $options;
        $this->coverage = new \PHP_CodeCoverage();
    }

    /**
     * @return bool
     */
    protected function getRemoteConnectionModule()
    {
        foreach (\Codeception\SuiteManager::$modules as $module) {
            if ($module instanceof \Codeception\Util\RemoteInterface) {
                return $module;
            }
        }
        return null;
    }

    public function beforeSuite(\Codeception\Event\Suite $e)
    {
        $settings = $e->getSettings();
        $this->applySettings($settings);

        $e->getResult()->setCodeCoverage(null);

        if (!$this->enabled or $this->remote) return;

        \Codeception\CodeCoverageSettings::setup($this->coverage)
            ->filterWhiteList($settings)
            ->filterBlackList($settings);

        $e->getResult()->setCodeCoverage($this->coverage);
    }

    /**
     * merge local code coverages
     * skip code coverage on remote server
     * fetch and merge
     *
     * @param \Codeception\Event\Suite $e
     */
    public function afterSuite(\Codeception\Event\Suite $e)
    {
        if (!$this->enabled or $this->remote) return;

        $coverage = $e->getResult()->getCodeCoverage();

        $remoteModule = $this->getRemoteConnectionModule();
        if (!$remoteModule) {
            $this->coverage->merge($coverage);
            return;
        };

        $externalCoverage = $this->getRemoteCoverageFile($this->getRemoteConnectionModule() ,'serialized');
        if (!$externalCoverage) return;
        $coverage = unserialize($externalCoverage);
        $this->coverage->merge($coverage);
    }

    protected function getRemoteCoverageFile($module, $type)
    {
        $headers = array('http' => array('header' => "X-Codeception-CodeCoverage: let me in\r\n"));
        $context = stream_context_create($headers);
        $url = $module->_getUrl() . '/c3/report/'.$type;
        return file_get_contents($url, null, $context);
    }


    public function printResult(\Codeception\Event\PrintResult $e)
    {
        $this->printText($e->getPrinter());
        if ($this->options['html']) $this->printHtml();
        if ($this->options['xml']) $this->printXml();
    }

    protected function printText($printer)
    {

        $writer = new \PHP_CodeCoverage_Report_Text(
            $printer, $this->settings['low_limit'], $this->settings['high_limit'], $this->settings['show_uncovered']
        );
        $writer->process($this->phpCodeCoverage, $this->config['settings']['colors']);
    }

    protected function printHtml()
    {
        $writer = new \PHP_CodeCoverage_Report_HTML(
            'UTF-8',
            true,
            $this->settings['low_limit'],
            $this->settings['high_limit'],
            sprintf(', <a href="http://codeception.com">Codeception</a> and <a href="http://phpunit.de/">PHPUnit %s</a>', \PHPUnit_Runner_Version::id()
            )
        );

        @mkdir(Configuration::logDir() . 'coverage');
        $writer->process($this->coverage, Configuration::logDir() . 'coverage');
    }

    protected function printXml()
    {
        $writer = new \PHP_CodeCoverage_Report_Clover;
        $writer->process($this->coverage, Configuration::logDir() . 'coverage.xml');
    }

    protected function applySettings($settings)
    {
        $keys = array_keys($this->settings);
        foreach ($keys as $key) {
            if (isset($config['coverage'][$key])) {
                $this->settings[$key] = $settings['coverage'][$key];
            }
        }
        $this->enabled = $this->settings['enabled'];
        $this->remote = $this->settings['remote'];
    }

    static function getSubscribedEvents()
    {
        return array(
            'suite.before' => 'beforeSuite',
            'suite.after' => 'afterSuite',
            'result.print.after' => 'printResult'
        );
    }


}