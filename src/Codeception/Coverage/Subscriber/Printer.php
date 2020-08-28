<?php
namespace Codeception\Coverage\Subscriber;

use Codeception\Configuration;
use Codeception\Coverage\Filter;
use Codeception\Coverage\PhpCodeCoverageFactory;
use Codeception\Event\PrintResultEvent;
use Codeception\Events;
use Codeception\Subscriber\Shared\StaticEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class Printer implements EventSubscriberInterface
{
    use StaticEvents;

    public static $events = [
        Events::RESULT_PRINT_AFTER => 'printResult'
    ];

    protected $settings = [
        'enabled'           => true,
        'low_limit'         => '35',
        'high_limit'        => '70',
        'show_uncovered'    => false,
        'show_only_summary' => false
    ];

    public static $coverage;
    protected $options;
    protected $logDir;
    protected $destination = [];

    public function __construct($options)
    {
        $this->options = $options;
        $this->logDir = Configuration::outputDir();
        $this->settings = array_merge($this->settings, Configuration::config()['coverage']);

        self::$coverage = PhpCodeCoverageFactory::build();

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
        $printer = $e->getPrinter();
        if (!$this->settings['enabled']) {
            $printer->write("\nCodeCoverage is disabled in `codeception.yml` config\n");
            return;
        }

        if (!$this->options['quiet']) {
            $this->printConsole($printer);
        }
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
        if ($this->options['coverage-crap4j']) {
            $this->printCrap4j();
            $printer->write("Crap4j report generated in {$this->options['coverage-crap4j']}\n");
        }
        if ($this->options['coverage-phpunit']) {
            $this->printPHPUnit();
            $printer->write("PHPUnit report generated in {$this->options['coverage-phpunit']}\n");
        }
    }

    protected function printConsole(\PHPUnit\Util\Printer $printer)
    {
        $writer = new \SebastianBergmann\CodeCoverage\Report\Text(
            $this->settings['low_limit'],
            $this->settings['high_limit'],
            $this->settings['show_uncovered'],
            $this->settings['show_only_summary']
        );
        $printer->write($writer->process(self::$coverage, $this->options['colors']));
    }

    protected function printHtml()
    {
        $writer = new \SebastianBergmann\CodeCoverage\Report\Html\Facade(
            $this->settings['low_limit'],
            $this->settings['high_limit'],
            sprintf(
                ', <a href="http://codeception.com">Codeception</a> and <a href="http://phpunit.de/">PHPUnit %s</a>',
                \PHPUnit\Runner\Version::id()
            )
        );

        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage-html']));
    }

    protected function printXml()
    {
        $writer = new \SebastianBergmann\CodeCoverage\Report\Clover();
        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage-xml']));
    }

    protected function printPHP()
    {
        $writer = new \SebastianBergmann\CodeCoverage\Report\PHP;
        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage']));
    }

    protected function printText()
    {
        $writer = new \SebastianBergmann\CodeCoverage\Report\Text(
            $this->settings['low_limit'],
            $this->settings['high_limit'],
            $this->settings['show_uncovered'],
            $this->settings['show_only_summary']
        );
        file_put_contents(
            $this->absolutePath($this->options['coverage-text']),
            $writer->process(self::$coverage, false)
        );
    }

    protected function printCrap4j()
    {
        $writer = new \SebastianBergmann\CodeCoverage\Report\Crap4j;
        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage-crap4j']));
    }

    protected function printPHPUnit()
    {
        $writer = new \SebastianBergmann\CodeCoverage\Report\Xml\Facade(\PHPUnit\Runner\Version::id());
        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage-phpunit']));
    }
}
