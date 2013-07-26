<?php
namespace Codeception\Command;

use Codeception\Codecept;
use Codeception\Configuration;
use Codeception\Exception\Configuration as ConfigurationException;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;


class GenerateScenarios extends Base
{
    protected function configure()
    {
        $this->setDefinition(array(
            new InputArgument('suite', InputArgument::REQUIRED, 'suite from which tests should be generated'),
            new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Use specified config instead of default'),
            new InputOption('path', 'p', InputOption::VALUE_REQUIRED, 'Use specified path as destination instead of default'),
            new InputOption('single-file', '', InputOption::VALUE_NONE, 'Render all scenarios to only one file'),
            new InputOption('format', 'f', InputOption::VALUE_REQUIRED, 'Specify output format: html or text (default)','text'),
            new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Use specified config instead of default'),
        ));
        parent::configure();
    }

    public function getDescription()
    {
        return 'Generates text representation for all scenarios';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');

        $suiteconf = $this->getSuiteConfig($suite, $input->getOption('config'));

        $path = $input->getOption('path')
            ? $input->getOption('path')
            : Configuration::dataDir() . 'scenarios';

        $format = $input->getOption('format');

        @mkdir($path);

        if (!is_writable($path)) {
            throw new ConfigurationException("Path $path is not writable. Please, set valid permissions for folder to store scenarios.");
        }

        $path = $path . DIRECTORY_SEPARATOR . $suite;
        if (!$input->getOption('single-file')) @mkdir($path);

        $suiteManager = new \Codeception\SuiteManager(new EventDispatcher(), $suite, $suiteconf);

        if ($suiteconf['bootstrap']) {
            if (file_exists($suiteconf['path'] . $suiteconf['bootstrap'])) {
                require_once $suiteconf['path'] . $suiteconf['bootstrap'];
            }
        }

        $tests = $this->getTests($suiteManager);
        $scenarios = "";

        foreach ($tests as $test) {
            if (!($test instanceof \Codeception\TestCase\Cept)) continue;
            $feature = $test->getScenarioText($format);

            $name = $test instanceof \Codeception\TestCase\Cest
                ? $this->underscore($test->getFileName())
                : $this->underscore(substr($test->getFileName(), 0, -8));

            if ($input->getOption('single-file')) {
                $scenarios .= $feature;
                $output->writeln("* $name rendered");
            } else {
                $feature = $this->decorate($feature, $format);
                $this->save($path . DIRECTORY_SEPARATOR . $name . $this->formatExtension($format), $feature);
                $output->writeln("* $name generated");
            }
        }

        if ($input->getOption('single-file')) {
            $this->save($path . $this->formatExtension($format), $this->decorate($scenarios, $format) . PHP_EOL);
        }
    }

    protected function decorate($text, $format)
    {
        switch ($format) {
            case 'text': return $text;
            case 'html': return "<html><body>$text</body></html>";
        }
    }

    protected function getTests($suiteManager)
    {
        $suiteManager->loadTests();
        return $suiteManager->getSuite()->tests();
    }

    protected function formatExtension($format)
    {
        switch ($format) {
            case 'text': return '.txt';
            case 'html': return '.html';
        }
    }

    private function underscore($name)
    {
        $name = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1_\\2', $name);
        $name = preg_replace('/([a-z\d])([A-Z])/', '\\1_\\2', $name);
        $name = str_replace(array('/','\\'),array('.','.'), $name);
        return $name;
    }

}
