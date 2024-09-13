<?php

declare(strict_types=1);

namespace Codeception\Coverage\Subscriber;

use Codeception\Configuration;
use Codeception\Coverage\Filter;
use Codeception\Coverage\PhpCodeCoverageFactory;
use Codeception\Event\PrintResultEvent;
use Codeception\Events;
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
use function file_put_contents;
use function str_starts_with;
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

    public function __construct(protected array $options, private readonly Output $output)
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
        if (str_starts_with($path, '/') || strpos($path, ':') === 1) { // absolute path
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
        if ($this->options['disable-coverage-php'] === true) {
            $this->output->write("PHP serialized report was skipped\n");
        } else {
            $this->printPHP();
        }
        $this->output->write("\n");

        $reports = [
            'HTML'      => ['name' => 'coverage-html',      'method' => 'printHTML'],
            'XML'       => ['name' => 'coverage-xml',       'method' => 'printXML'],
            'Text'      => ['name' => 'coverage-text',      'method' => 'printText'],
            'Crap4j'    => ['name' => 'coverage-crap4j',    'method' => 'printCrap4j'],
            'Cobertura' => ['name' => 'coverage-cobertura', 'method' => 'printCobertura'],
            'PHPUnit'   => ['name' => 'coverage-phpunit',   'method' => 'printPHPUnit'],
        ];

        foreach ($reports as $reportType => $reportData) {
            if ($option = $this->options[$reportData['name']]) {
                $this->{$reportData['method']}();
                $this->output->write("{$reportType} report generated in {$option}\n");
            }
        }
    }

    protected function printConsole(): void
    {
        $writer = $this->createTextWriter();
        $this->output->write($writer->process(self::$coverage, $this->options['colors']));
    }

    protected function printHtml(): void
    {
        $writer = $this->createHtmlFacadeWriter();
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
        $writer = $this->createTextWriter();
        file_put_contents(
            $this->absolutePath($this->options['coverage-text']),
            $writer->process(self::$coverage)
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

    private function createHtmlFacadeWriter(): HtmlFacadeReport
    {
        $generator = ', <a href="https://codeception.com">Codeception</a> and <a href="https://phpunit.de/">PHPUnit {PHPUnitVersion::id()}</a>';
        return PHPUnitVersion::series() < 10 ?
            new HtmlFacadeReport($this->settings['low_limit'], $this->settings['high_limit'], $generator) :
            new HtmlFacadeReport($generator, null, Thresholds::from($this->settings['low_limit'], $this->settings['high_limit']));
    }

    private function createTextWriter(): TextReport
    {
        return PHPUnitVersion::series() < 10 ?
            new TextReport($this->settings['low_limit'], $this->settings['high_limit'], $this->settings['show_uncovered'], $this->settings['show_only_summary']) :
            new TextReport(Thresholds::from($this->settings['low_limit'], $this->settings['high_limit']), $this->settings['show_uncovered'], $this->settings['show_only_summary']);
    }
}
