<?php

declare(strict_types=1);

namespace Codeception\Coverage\Subscriber;

use Codeception\Configuration;
use Codeception\Coverage\Filter;
use Codeception\Coverage\PhpCodeCoverageFactory;
use Codeception\Event\PrintResultEvent;
use Codeception\Events;
use Codeception\Exception\ConfigurationException;
use Codeception\Subscriber\Shared\StaticEventsTrait;
use PHPUnit\Runner\Version as PHPUnitVersion;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover as CloverReport;
use SebastianBergmann\CodeCoverage\Report\Cobertura as CoberturaReport;
use SebastianBergmann\CodeCoverage\Report\Crap4j as Crap4jReport;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlFacadeReport;
use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;
use SebastianBergmann\CodeCoverage\Report\Text as TextReport;
use SebastianBergmann\CodeCoverage\Report\Xml\Facade as XmlFacadeReport;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use function array_merge;
use function class_exists;
use function file_put_contents;
use function sprintf;
use function strpos;

class Printer implements EventSubscriberInterface
{
    use StaticEventsTrait;

    /**
     * @var array<string, string>
     */
    public static array $events = [
        Events::RESULT_PRINT_AFTER => 'printResult'
    ];

    protected array $settings = [
        'enabled'           => true,
        'low_limit'         => 35,
        'high_limit'        => 70,
        'show_uncovered'    => false,
        'show_only_summary' => false
    ];

    public static CodeCoverage $coverage;

    protected array $options = [];

    protected string $logDir;

    protected array $destination = [];

    public function __construct(array $options)
    {
        $this->options = $options;
        $this->logDir = Configuration::outputDir();
        $this->settings = array_merge($this->settings, Configuration::config()['coverage']);

        self::$coverage = PhpCodeCoverageFactory::build();

        // Apply filter
        $filter = new Filter(self::$coverage);
        $filter->whiteList(Configuration::config());
        $filter->blackList(Configuration::config());
    }

    protected function absolutePath(string $path): string
    {
        if ((strpos($path, '/') === 0) || (strpos($path, ':') === 1)) { // absolute path
            return $path;
        }
        return $this->logDir . $path;
    }

    public function printResult(PrintResultEvent $event): void
    {
        $printer = $event->getPrinter();
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
        if ($this->options['coverage-cobertura']) {
            $this->printCobertura();
            $printer->write("Cobertura report generated in {$this->options['coverage-cobertura']}\n");
        }
        if ($this->options['coverage-phpunit']) {
            $this->printPHPUnit();
            $printer->write("PHPUnit report generated in {$this->options['coverage-phpunit']}\n");
        }
    }

    protected function printConsole(\PHPUnit\Util\Printer $printer): void
    {
        $writer = new TextReport(
            $this->settings['low_limit'],
            $this->settings['high_limit'],
            $this->settings['show_uncovered'],
            $this->settings['show_only_summary']
        );
        $printer->write($writer->process(self::$coverage, $this->options['colors']));
    }

    protected function printHtml(): void
    {
        $writer = new HtmlFacadeReport(
            $this->settings['low_limit'],
            $this->settings['high_limit'],
            sprintf(
                ', <a href="https://codeception.com">Codeception</a> and <a href="https://phpunit.de/">PHPUnit %s</a>',
                PHPUnitVersion::id()
            )
        );

        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage-html']));
    }

    protected function printXml(): void
    {
        $writer = new CloverReport();
        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage-xml']));
    }

    protected function printPHP(): void
    {
        $writer = new PhpReport();
        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage']));
    }

    protected function printText(): void
    {
        $writer = new TextReport(
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

    protected function printCrap4j(): void
    {
        $writer = new Crap4jReport();
        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage-crap4j']));
    }

    protected function printCobertura(): void
    {
        if (!class_exists(CoberturaReport::class)) {
            throw new ConfigurationException("Cobertura report requires php-code-coverage >= 9.2");
        }
        $writer = new CoberturaReport();
        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage-cobertura']));
    }

    protected function printPHPUnit(): void
    {
        $writer = new XmlFacadeReport(PHPUnitVersion::id());
        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage-phpunit']));
    }
}
