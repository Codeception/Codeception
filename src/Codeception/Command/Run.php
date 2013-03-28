<?php
namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Run extends Base
{

    protected function configure()
    {
        $this->setDefinition(array(

            new InputArgument('suite', InputArgument::OPTIONAL, 'suite to be tested'),
            new InputArgument('test', InputArgument::OPTIONAL, 'test to be run'),

            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
            new InputOption('report', '', InputOption::VALUE_NONE, 'Show output in compact style'),
            new InputOption('html', '', InputOption::VALUE_NONE, 'Generate html with results'),
            new InputOption('xml', '', InputOption::VALUE_NONE, 'Generate JUnit XML Log'),
            new InputOption('tap', '', InputOption::VALUE_NONE, 'Generate Tap Log'),
            new InputOption('json', '', InputOption::VALUE_NONE, 'Generate Json Log'),
            new InputOption('colors', '', InputOption::VALUE_NONE, 'Use colors in output'),
            new InputOption('silent', '', InputOption::VALUE_NONE, 'Don\'t show the progress output'),
            new InputOption('steps', '', InputOption::VALUE_NONE, 'Show steps in output'),
            new InputOption('debug', '', InputOption::VALUE_NONE, 'Show debug and scenario output'),
            new InputOption('coverage', 'cc', InputOption::VALUE_NONE, 'Run with code coverage'),
            new InputOption('no-exit', '', InputOption::VALUE_NONE, 'Don\'t finish with exit code')
        ));
        parent::configure();
    }

    public function getDescription()
    {
        return 'Runs the test suites';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(\Codeception\Codecept::versionString() . "\nPowered by " . \PHPUnit_Runner_Version::getVersionString());
        $options = $input->getOptions();
        if ($input->getOption('debug')) $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);
        if ($input->getArgument('test')) $options['steps'] = true;

        $suite = $input->getArgument('suite');
        $test = $input->getArgument('test');

        $codecept = new \Codeception\Codecept((array) $options);
        $config = \Codeception\Configuration::config();

        if (strpos($suite, $config['paths']['tests'])===0) {
            $matches = $this->matchTestFromFilename($suite, $config['paths']['tests']);
            $suite = $matches[1];
            $test = $matches[2];
        }

        $suites = $suite ? array($suite) : \Codeception\Configuration::suites();

        if ($suite and $test) {
            $codecept->runSuite($suite, $test);
        }

        if (!$test) {
            foreach ($suites as $suite) {
                $codecept->runSuite($suite);
            }
        }

        $codecept->printResult();

        if (!$input->getOption('no-exit')) {
            if ($codecept->getResult()->failureCount() or $codecept->getResult()->errorCount()) exit(1);
        }
    }


    protected function matchTestFromFilename($filename,$tests_path)
    {
        $filename = str_replace('\/','/', $filename);
        $res = preg_match("~^$tests_path/(.*?)/(.*)$~", $filename, $matches);
        if (!$res) throw new \InvalidArgumentException("Test file can't be matched");
        return $matches;
    }

}