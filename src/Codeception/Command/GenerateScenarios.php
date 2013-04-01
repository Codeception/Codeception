<?php
namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateScenarios extends Base
{
    protected function configure()
    {
        $this->setDefinition(array(
            new InputArgument('suite', InputArgument::REQUIRED, 'suite from which tests should be generated'),
            new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Use specified config instead of default'),
            new InputOption('path', 'p', InputOption::VALUE_REQUIRED, 'Use specified path as destination instead of default'),
            new InputOption('single-file', '', InputOption::VALUE_NONE, 'Render all scenarios to only one file'),
            new InputOption('format', 'f', InputOption::VALUE_REQUIRED, 'Specify output format: html or text (default)'),
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

        $config = \Codeception\Configuration::config($input->getOption('config'));
        $suiteconf = \Codeception\Configuration::suiteSettings($suite, $config);

        if ($input->getOption('path')) {
            $path = $input->getOption('path');
        } else {
            $path = \Codeception\Configuration::dataDir() . 'scenarios';
        }

        @mkdir($path);

        if (!is_writable($path)) {
            throw new \Codeception\Exception\Configuration("Path for logs is not writable. Please, set appropriate access mode for log path.");
        }

        $path = $path . DIRECTORY_SEPARATOR . $suite;

        if ($input->getOption('single-file')) {
            file_put_contents($path . '.txt', '');
        } else {
            @mkdir($path);
        }

            $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();

        $suiteManager = new \Codeception\SuiteManager($dispatcher, $suite, $suiteconf);

        if (isset($suiteconf['bootstrap'])) {
            if (file_exists($suiteconf['path'] . $suiteconf['bootstrap'])) {
                require_once $suiteconf['path'] . $suiteconf['bootstrap'];
            }
        }

        $suiteManager->loadTests();
        $tests = $suiteManager->getSuite()->tests();

        if ($input->getOption('format')) {
            $format = $input->getOption('format');
        } else {
            $format = 'text';
        }

        foreach ($tests as $test) {
            if (!($test instanceof \Codeception\TestCase\Cept)) continue;
            $test->testCodecept(false);
            $features = $test->getScenarioText($format);
            $name = $this->underscore(substr($test->getFileName(), 0, -8));

            if ($input->getOption('single-file')) {
                file_put_contents($path . '.txt', $features . PHP_EOL, FILE_APPEND);
                $output->writeln("* $name rendered");
            } else {
                file_put_contents($path . DIRECTORY_SEPARATOR . $name . '.txt', $features);
                $output->writeln("* $name generated");
            }
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
