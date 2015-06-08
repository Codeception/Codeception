<?php
namespace Codeception\Coverage\Subscriber;

use Codeception\Configuration;
use Codeception\Coverage\Filter;
use Codeception\Event\PrintResultEvent;
use Codeception\Events;
use Codeception\Subscriber\Shared\StaticEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Printer implements EventSubscriberInterface
{
    use StaticEvents;

    static $events = [
        Events::RESULT_PRINT_AFTER => 'printResult'
    ];

    protected $settings = [
        'enabled'        => true,
        'low_limit'      => '35',
        'high_limit'     => '70',
        'show_uncovered' => false
    ];

    static $coverage;
    protected $options;
    protected $logDir;
    protected $destination = [];

    public function __construct($options)
    {
        $this->options = $options;
        $this->logDir = Configuration::outputDir();
        $this->settings = array_merge($this->settings, Configuration::config()['coverage']);
        self::$coverage = new \PHP_CodeCoverage();

        // Apply filter
        $filter = new Filter(self::$coverage);
        $filter
            ->whiteList(Configuration::config())
            ->blackList(Configuration::config());
    }

    protected function absolutePath($path)
    {
        if ((strpos($path, '/') === 0) || (strpos($path, ':') === 1)) { // absolute path
            return $path;
        }
        return $this->logDir . $path;
    }

    public function printResult(PrintResultEvent $e)
    {
        if ($this->options['steps']) {
            return;
        }
        $printer = $e->getPrinter();
        if (!$this->settings['enabled']) {
            $printer->write("\nCodeCoverage is disabled in `codeception.yml` config\n");
            return;
        }

        $this->printConsole($printer);
        $printer->write("Remote CodeCoverage reports are not printed to console\n");
        $this->printPHP();
        $printer->write("\n");
        if ($this->options['coverage-html']) {
            $this->printHtml();
            $printer->write("HTML report generated in {$this->options['coverage-html']}\n");
        }
        if ($this->options['coverage-xml']) {
            $this->printXml();
            $printer->write("XML report generated in {$this->options['coverage-xml']}\n");
        }
        if ($this->options['coverage-text']) {
            $this->printText();
            $printer->write("Text report generated in {$this->options['coverage-text']}\n");
        }

    }

    protected function printConsole(\PHPUnit_Util_Printer $printer)
    {
        $writer = new \PHP_CodeCoverage_Report_Text(
            $this->settings['low_limit'],
            $this->settings['high_limit'],
            $this->settings['show_uncovered'],
            false
        );
        $printer->write($writer->process(self::$coverage, $this->options['colors']));
    }

    protected function printHtml()
    {
        $writer = new \PHP_CodeCoverage_Report_HTML(
            $this->settings['low_limit'],
            $this->settings['high_limit'],
            sprintf(
                ', <a href="http://codeception.com">Codeception</a> and <a href="http://phpunit.de/">PHPUnit %s</a>',
                \PHPUnit_Runner_Version::id()
            )
        );

        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage-html']));
    }

    protected function printXml()
    {
        $writer = new \PHP_CodeCoverage_Report_Clover;
        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage-xml']));
    }

    protected function printPHP()
    {
        $writer = new \PHP_CodeCoverage_Report_PHP;
        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage']));
    }

    protected function printText()
    {
        $writer = new \PHP_CodeCoverage_Report_Text(
            $this->settings['low_limit'], $this->settings['high_limit'], $this->settings['show_uncovered'], false
        );
        file_put_contents($this->absolutePath($this->options['coverage-text']), $writer->process(self::$coverage, false));
    }
}
