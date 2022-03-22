<?php

declare(strict_types=1);

namespace Codeception\Coverage\Subscriber;

use Codeception\Configuration;
use Codeception\Coverage\Filter;
use Codeception\Coverage\PhpCodeCoverageFactory;
use Codeception\Event\PrintResultEvent;
use Codeception\Events;
use Codeception\Exception\ConfigurationException;
use Codeception\Lib\Console\Output;
use Codeception\Subscriber\Shared\StaticEventsTrait;
use PHPUnit\Runner\Version as PHPUnitVersion;
use SebastianBergmann\CodeCoverage\CodeCoverage;
use SebastianBergmann\CodeCoverage\Report\Clover as CloverReport;
use SebastianBergmann\CodeCoverage\Report\Cobertura as CoberturaReport;
use SebastianBergmann\CodeCoverage\Report\Crap4j as Crap4jReport;
use SebastianBergmann\CodeCoverage\Report\Html\Facade as HtmlFacadeReport;
use SebastianBergmann\CodeCoverage\Report\PHP as PhpReport;
use SebastianBergmann\CodeCoverage\Report\Text as TextReport;
use SebastianBergmann\CodeCoverage\Report\Thresholds;
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

    protected string $logDir;

    public function __construct(protected array $options, private Output $output)
    {
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
        if ((str_starts_with($path, '/')) || (strpos($path, ':') === 1)) { // absolute path
            return $path;
        }
        return $this->logDir . $path;
    }

    public function printResult(PrintResultEvent $event): void
    {
        if (!$this->settings['enabled']) {
            $this->output->write("\nCodeCoverage is disabled in `codeception.yml` config\n");
            return;
        }

        if (!$this->options['quiet']) {
            $this->printConsole();
        }
        $this->output->write("Remote CodeCoverage reports are not printed to console\n");
        $this->printPHP();
        $this->output->write("\n");
        if ($this->options['coverage-html']) {
            $this->printHtml();
            $this->output->write("HTML report generated in {$this->options['coverage-html']}\n");
        }
        if ($this->options['coverage-xml']) {
            $this->printXml();
            $this->output->write("XML report generated in {$this->options['coverage-xml']}\n");
        }
        if ($this->options['coverage-text']) {
            $this->printText();
            $this->output->write("Text report generated in {$this->options['coverage-text']}\n");
        }
        if ($this->options['coverage-crap4j']) {
            $this->printCrap4j();
            $this->output->write("Crap4j report generated in {$this->options['coverage-crap4j']}\n");
        }
        if ($this->options['coverage-cobertura']) {
            $this->printCobertura();
            $this->output->write("Cobertura report generated in {$this->options['coverage-cobertura']}\n");
        }
        if ($this->options['coverage-phpunit']) {
            $this->printPHPUnit();
            $this->output->write("PHPUnit report generated in {$this->options['coverage-phpunit']}\n");
        }
    }

    protected function printConsole(): void
    {
        if (PHPUnitVersion::series() < 10) {
            $writer = new TextReport(
                $this->settings['low_limit'],
                $this->settings['high_limit'],
                $this->settings['show_uncovered'],
                $this->settings['show_only_summary']
            );
        } else {
            $writer = new TextReport(
                Thresholds::from(
                    $this->settings['low_limit'],
                    $this->settings['high_limit'],
                ),
                $this->settings['show_uncovered'],
                $this->settings['show_only_summary']
            );
        }
        $this->output->write($writer->process(self::$coverage, $this->options['colors']));
    }

    protected function printHtml(): void
    {
        if (PHPUnitVersion::series() < 10) {
            $writer = new HtmlFacadeReport(
                $this->settings['low_limit'],
                $this->settings['high_limit'],
                sprintf(
                    ', <a href="https://codeception.com">Codeception</a> and <a href="https://phpunit.de/">PHPUnit %s</a>',
                    PHPUnitVersion::id()
                )
            );
        } else {
            $writer = new HtmlFacadeReport(
                sprintf(
                    ', <a href="https://codeception.com">Codeception</a> and <a href="https://phpunit.de/">PHPUnit %s</a>',
                    PHPUnitVersion::id()
                ),
                null,
                Thresholds::from($this->settings['low_limit'], $this->settings['high_limit']),
            );
        }

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
        if (PHPUnitVersion::series() < 10) {
            $writer = new TextReport(
                $this->settings['low_limit'],
                $this->settings['high_limit'],
                $this->settings['show_uncovered'],
                $this->settings['show_only_summary']
            );
        } else {
            $writer = new TextReport(
                Thresholds::from(
                    $this->settings['low_limit'],
                    $this->settings['high_limit'],
                ),
                $this->settings['show_uncovered'],
                $this->settings['show_only_summary']
            );
        }

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
        $writer = new CoberturaReport();
        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage-cobertura']));
    }

    protected function printPHPUnit(): void
    {
        $writer = new XmlFacadeReport(PHPUnitVersion::id());
        $writer->process(self::$coverage, $this->absolutePath($this->options['coverage-phpunit']));
    }
}
