<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class Run extends Base
{

    /**
     * @var \Codeception\Codecept
     */
    protected $codecept;

    protected function configure()
    {
        $this->setDefinition(array(

            new InputArgument('suite', InputArgument::OPTIONAL, 'suite to be tested'),
            new InputArgument('test', InputArgument::OPTIONAL, 'test to be run'),

            new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Use custom path for config'),
            new InputOption('report', '', InputOption::VALUE_NONE, 'Show output in compact style'),
            new InputOption('html', '', InputOption::VALUE_NONE, 'Generate html with results'),
            new InputOption('xml', '', InputOption::VALUE_NONE, 'Generate JUnit XML Log'),
            new InputOption('tap', '', InputOption::VALUE_NONE, 'Generate Tap Log'),
            new InputOption('json', '', InputOption::VALUE_NONE, 'Generate Json Log'),
            new InputOption('colors', '', InputOption::VALUE_NONE, 'Use colors in output'),
            new InputOption('no-colors', '', InputOption::VALUE_NONE, 'Force no colors in output (useful to override config file)'),
            new InputOption('silent', '', InputOption::VALUE_NONE, 'Only outputs suite names and final results'),
            new InputOption('steps', '', InputOption::VALUE_NONE, 'Show steps in output'),
            new InputOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug and scenario output'),
            new InputOption('coverage', 'cc', InputOption::VALUE_NONE, 'Run with code coverage'),
            new InputOption('no-exit', '', InputOption::VALUE_NONE, 'Don\'t finish with exit code'),
            new InputOption('defer-flush', '', InputOption::VALUE_NONE, 'Don\'t flush output during run'),
            new InputOption('group', 'g', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Groups of tests to be executed'),
            new InputOption('skip', 's', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Skip selected suites'),
            new InputOption('skip-group', 'sg', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Skip selected groups'),
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
        if ($options['debug']) $output->setVerbosity(OutputInterface::VERBOSITY_VERBOSE);

        $config = Configuration::config($options['config']);

        $suite = $input->getArgument('suite');
        $test = $input->getArgument('test');

        if (!Configuration::isEmpty() && !$test && strpos($suite, $config['paths']['tests'])===0) {
            list($matches, $suite, $test) = $this->matchTestFromFilename($suite, $config['paths']['tests']);
        }

        if ($options['group']) $output->writeln(sprintf("[Groups] <info>%s</info> ", implode(', ', $options['group'])));
        if ($input->getArgument('test')) $options['steps'] = true;

        $this->codecept = new \Codeception\Codecept((array) $options);

        if ($suite and $test) $this->codecept->runSuite($suite, $test);

        if (!$test) {
            $suites = $suite ? explode(',', $suite) : Configuration::suites();
            $current_dir = Configuration::projectDir();
            $this->runSuites($suites, $options['skip']);
            foreach ($config['include'] as $included_config_file) {
                Configuration::config($full_path = $current_dir . $included_config_file);
                $namespace = $this->currentNamespace();
                $output->writeln("\n<fg=white;bg=magenta>\n[$namespace]: tests from $full_path\n</fg=white;bg=magenta>");
                $suites = $suite ? explode(',', $suite) : Configuration::suites();
                $this->runSuites($suites, $options['skip']);
            }
        }

        $this->codecept->printResult();

        if (!$input->getOption('no-exit')) {
            if ($this->codecept->getResult()->failureCount() or $this->codecept->getResult()->errorCount()) exit(1);
        }
    }

    protected function currentNamespace()
    {
        $config = Configuration::config();
        if (!$config['namespace'])
            throw new \RuntimeException("Can't include into runner suite without a namespace;\nUse 'refactor:add-namespace' command to fix it'");
        return $config['namespace'];
    }

    protected function runSuites($suites, $skippedSuites = array())
    {
        foreach ($suites as $suite) {
            if (in_array($suite, $skippedSuites)) continue;
            if (!in_array($suite, Configuration::suites())) continue;
            $this->codecept->runSuite($suite);
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
