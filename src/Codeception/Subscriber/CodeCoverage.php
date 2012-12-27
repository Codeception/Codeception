<?php
namespace Codeception\Subscriber;

use \Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Retrieves CodeCoverage data from remote server
 */

class CodeCoverage implements EventSubscriberInterface
{
    /**
     * @var \Codeception\Util\RemoteInterface
     */
    protected $client = null;
    protected $coverage = null;
    protected $options = array();

    function __construct(\Codeception\CodeCoverage $coverage, $options = array())
    {
        $this->coverage = $coverage;
        $this->options = $options;
    }

    public function beforeSuite(\Codeception\Event\Suite $e)
    {
        // if not disabled
        $settings = \Codeception\Configuration::suiteSettings($e->getSuite()->getName(), \Codeception\Configuration::config());

        if (isset($settings['coverage'])) {
            if (isset($settings['coverage']['enabled'])) {
                if (!$settings['coverage']['enabled']) $e->getResult()->setCodeCoverage(null);
            }
        }


    }

    public function afterSuite(\Codeception\Event\Suite $e)
    {
        if (!$this->coverage->isRemote()) return;
        foreach (\Codeception\SuiteManager::$modules as $module) {
            if ($module instanceof \Codeception\Util\RemoteInterface) {
                $this->client = $module;
            }

        }
        if (!$this->client) return;

        $suite = $e->getName();

        // if remote && html -> print html
        // if remote && clover -> print xml
        // if local -> merge

        // Create a stream
        $options = array(
            'http' => array('header' => "X-Codeception-CodeCoverage: let me in\r\n")
        );

        if ($this->coverage->isLocal()) {

            return;
        }

        if ($this->options['xml']) $this->retrieveAndPrintXml($suite, $options);
        if ($this->options['html']) $this->retrieveAndPrintHtml($suite, $options);
    }

    protected function mergeLocal()
    {

    }

    protected function retrieveAndPrintHtml($suite, $headers)
    {
        $context = stream_context_create($headers);
        $url = $this->client->_getUrl() . '/c3/report/html';

        $tempFile = str_replace('.', '', tempnam(sys_get_temp_dir(), 'C3')) . '.tar';
        file_put_contents($tempFile, file_get_contents($url, null, $context));

        $destDir = \Codeception\Configuration::logDir() . $suite . '.remote.codecoverage';

        if (!is_dir($destDir)) {
            mkdir($destDir, 0777, true);
        } else {
            \Codeception\Util\FileSystem::doEmptyDir($destDir);
        }

        $phar = new \PharData($tempFile);
        $phar->extractTo($destDir);

        unlink($tempFile);
    }

    protected function printXml()
    {

    }

    static function getSubscribedEvents()
    {
        return array(
            'suite.after' => 'afterSuite',
        );
    }


}