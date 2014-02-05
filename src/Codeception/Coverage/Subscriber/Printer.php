<?php
namespace Codeception\Coverage\Subscriber;

use Codeception\CodeceptionEvents;
use Codeception\Configuration;
use Codeception\Event\PrintResultEvent;
use Codeception\Subscriber\Shared\StaticEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Printer implements EventSubscriberInterface {
    use StaticEvents;

    static $events = [
        CodeceptionEvents::RESULT_PRINT_AFTER => 'printResult'
    ];

    protected $settings = [
        'low_limit' => '35',
        'high_limit' => '70',
        'show_uncovered' => false
    ];

    static $coverage;
    protected $options;
    protected $logDir;

    public function __construct($options)
    {
        $this->options = $options;
        $this->logDir = Configuration::logDir();
        $this->settings = array_merge($this->settings, Configuration::config()['coverage']);
        self::$coverage = new \PHP_CodeCoverage();

    }

    public function printResult(PrintResultEvent $e)
    {
        if ($this->options['steps']) {
            return;
        }

        $this->printText($e->getPrinter());
        $this->printPHP();
        if ($this->options['html']) {
            $this->printHtml();
        }
        if ($this->options['xml']) {
            $this->printXml();
        }
    }

    protected function printText(\PHPUnit_Util_Printer $printer)
    {
        $writer = new \PHP_CodeCoverage_Report_Text($printer,
            $this->settings['low_limit'], $this->settings['high_limit'], $this->settings['show_uncovered']
        );
        $printer->write($writer->process(self::$coverage, $this->options['colors']));
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

        $writer->process(self::$coverage, $this->logDir . 'coverage');
    }

    protected function printXml()
    {
        $writer = new \PHP_CodeCoverage_Report_Clover;
        $writer->process(self::$coverage, $this->logDir . 'coverage.xml');
    }

    protected function printPHP()
    {
        $writer = new \PHP_CodeCoverage_Report_PHP;
        $writer->process(self::$coverage, $this->logDir. 'coverage.serialized');
    }

}