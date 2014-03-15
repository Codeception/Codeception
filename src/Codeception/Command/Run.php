<?php

namespace Codeception\Command;

use Codeception\Codecept;
use Codeception\Configuration;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Executes tests.
 *
 * `php codecept.phar run` - executes all tests in all suites
 * `php codecept.phar run tests/suitename/testnameCept.php` - executes test by it's path.
 * `php codecept.phar run tests/suitename/foldername` - executes tests from folder.
 * `php codecept.phar run suitename` - executes all test from this suite
 * `php codecept.phar run suitename testnameTest.php` - executes one test of this suite (provide local path for
suite directory).
 * `php codecept.phar run -g admin` - executes tests by group.
 *
 * ```
 * 	Usage:
 * 	 run [-c|--config="..."] [--report] [--html] [--xml] [--tap] [--json] [--colors] [--no-colors] [--silent] [--steps] [-d|--debug] [--coverage] [--no-exit] [-g|--group="..."] [-s|--skip="..."] [--skip-group="..."] [--env="..."] [suite] [test]
 *
 * 	Arguments:
 * 	 suite                 suite to be tested
 * 	 test                  test to be run
 *
 * 	Options:
 * 	 --config (-c)         Use custom path for config
 * 	 --report              Show output in compact style
 * 	 --html                Generate html with results
 * 	 --xml                 Generate JUnit XML Log
 * 	 --tap                 Generate Tap Log
 * 	 --json                Generate Json Log
 * 	 --colors              Use colors in output
 * 	 --no-colors           Force no colors in output (useful to override config file)
 * 	 --silent              Only outputs suite names and final results
 * 	 --steps               Show steps in output
 * 	 --debug (-d)          Show debug and scenario output
 * 	 --coverage            Run with code coverage
 * 	 --no-exit             Don't finish with exit code
 * 	 --group (-g)          Groups of tests to be executed (multiple values allowed)
 * 	 --skip (-s)           Skip selected suites (multiple values allowed)
 * 	 --skip-group          Skip selected groups (multiple values allowed)
 * 	 --env                 Run tests in selected environments. (multiple values allowed)
 * 	 --help (-h)           Display this help message.
 * 	 --quiet (-q)          Do not output any message.
 * 	 --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 * 	 --version (-V)        Display this application version.
 * 	 --ansi                Force ANSI output.
 * 	 --no-ansi             Disable ANSI output.
 * 	 --no-interaction (-n) Do not ask any interactive question.
 * ```
 *
 */
class Run extends Base
{
    /**
     * @var Codecept
     */
    protected $codecept;

    protected function configure()
    {
        $this->setDefinition(
             array(
                 new InputArgument('suite', InputArgument::OPTIONAL, 'suite to be tested'),
                 new InputArgument('test', InputArgument::OPTIONAL, 'test to be run'),
                 new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Use custom path for config'),
                 new InputOption('report', '', InputOption::VALUE_NONE, 'Show output in compact style'),
                 new InputOption('html', '', InputOption::VALUE_OPTIONAL, 'Generate html with results', 'result.html'),
                 new InputOption('xml', '', InputOption::VALUE_OPTIONAL, 'Generate JUnit XML Log', 'result.xml'),
                 new InputOption('tap', '', InputOption::VALUE_OPTIONAL, 'Generate Tap Log', 'result.tap.log'),
                 new InputOption('json', '', InputOption::VALUE_OPTIONAL, 'Generate Json Log', 'result.json'),
                 new InputOption('colors', '', InputOption::VALUE_NONE, 'Use colors in output'),
                 new InputOption('no-colors', '', InputOption::VALUE_NONE, 'Force no colors in output (useful to override config file)'),
                 new InputOption('silent', '', InputOption::VALUE_NONE, 'Only outputs suite names and final results'),
                 new InputOption('steps', '', InputOption::VALUE_NONE, 'Show steps in output'),
                 new InputOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug and scenario output'),
                 new InputOption('coverage', '', InputOption::VALUE_OPTIONAL, 'Run with code coverage', 'coverage.serialized'),
                 new InputOption('coverage-html', '', InputOption::VALUE_OPTIONAL, 'Generate CodeCoverage HTML report in path', 'coverage'),
                 new InputOption('coverage-xml', '', InputOption::VALUE_OPTIONAL, 'Generate CodeCoverage XML report in file', 'coverage.xml'),
                 new InputOption('no-exit', '', InputOption::VALUE_NONE, 'Don\'t finish with exit code'),
                 new InputOption('group', 'g', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Groups of tests to be executed'),
                 new InputOption('skip', 's', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Skip selected suites'),
                 new InputOption('skip-group', 'sg', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Skip selected groups'),
                 new InputOption('env', '', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Run tests in selected environments.'),
             )
        );

        parent::configure();
    }

    public function getDescription()
    {
        return 'Runs the test suites';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln(Codecept::versionString() . "\nPowered by " . \PHPUnit_Runner_Version::getVersionString());

        $options = $input->getOptions();
        $options = array_merge($options, $this->booleanOptions($input, ['xml','html','coverage','coverage-xml','coverage-html']));
        if ($options['debug']) {
            $output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        }
        $options['verbosity'] = $output->getVerbosity();

        $this->ensureCurlIsAvailable();

        $config = Configuration::config($options['config']);

        $suite = $input->getArgument('suite');
        $test  = $input->getArgument('test');

        if (! Configuration::isEmpty() && ! $test && strpos($suite, $config['paths']['tests']) === 0) {
            list($matches, $suite, $test) = $this->matchTestFromFilename($suite, $config['paths']['tests']);
        }

        if ($options['group']) {
            $output->writeln(sprintf("[Groups] <info>%s</info> ", implode(', ', $options['group'])));
        }
        if ($input->getArgument('test')) {
            $options['steps'] = true;
        }

        if ($test) {
            $filter            = $this->matchFilteredTestName($test);
            $options['filter'] = $filter;
        }

        $this->codecept = new Codecept((array)$options);

        if ($suite and $test) {
            $this->codecept->run($suite, $test);
        }

        if (! $test) {
            $suites      = $suite ? explode(',', $suite) : Configuration::suites();
            $current_dir = Configuration::projectDir();
            $executed    = $this->runSuites($suites, $options['skip']);
            foreach ($config['include'] as $included_config_file) {
                Configuration::config($full_path = $current_dir . $included_config_file);
                $namespace = $this->currentNamespace();
                $output->writeln(
                       "\n<fg=white;bg=magenta>\n[$namespace]: tests from $full_path\n</fg=white;bg=magenta>"
                );
                $suites = $suite ? explode(',', $suite) : Configuration::suites();
                $executed += $this->runSuites($suites, $options['skip']);
            }
            if (! $executed) {
                throw new \RuntimeException(
                    sprintf("Suite '%s' could not be found", implode(', ', $suites))
                );
            }
        }

        $this->codecept->printResult();

        if (! $input->getOption('no-exit')) {
            if (! $this->codecept->getResult()->wasSuccessful()) {
                exit(1);
            }
        }
    }

    protected function currentNamespace()
    {
        $config = Configuration::config();
        if (! $config['namespace']) {
            throw new \RuntimeException(
                "Can't include into runner suite without a namespace;\n"
                . "Use 'refactor:add-namespace' command to fix it'"
            );
        }

        return $config['namespace'];
    }

    protected function runSuites($suites, $skippedSuites = array())
    {
        $executed = 0;
        foreach ($suites as $suite) {
            if (in_array($suite, $skippedSuites)) {
                continue;
            }
            if (! in_array($suite, Configuration::suites())) {
                continue;
            }
            $this->codecept->run($suite);
            $executed ++;
        }

        return $executed;
    }

    protected function matchTestFromFilename($filename, $tests_path)
    {
        $filename = str_replace('\/', '/', $filename);
        $res      = preg_match("~^$tests_path/(.*?)/(.*)$~", $filename, $matches);
        if (! $res) {
            throw new \InvalidArgumentException("Test file can't be matched");
        }

        return $matches;
    }

    private function matchFilteredTestName(&$path)
    {
        $test_parts = explode(':', $path);
        if (count($test_parts) > 1) {
            list($path, $filter) = $test_parts;
            return $filter;
        }

        return null;
    }

    protected function booleanOptions(InputInterface $input, $options = [])
    {
        $values = [];
        $request = (string) $input;
        foreach ($options as $option) {
            if (strpos($request, "--$option")) {
                $values[$option] = $input->hasParameterOption($option)
                    ? $input->getParameterOption($option)
                    : $input->getOption($option);
            } else {
                $values[$option] = false;
            }
        }
        return $values;
    }

    private function ensureCurlIsAvailable()
    {
        if (! extension_loaded('curl')) {
            throw new \Exception(
                "Codeception requires CURL extension installed to make tests run\n" .
                "If you are not sure, how to install CURL, please refer to StackOverflow\n\n" .
                "Notice: PHP for Apache/Nginx and CLI can have different php.ini files.\n" .
                "Please make sure that your PHP you run from console has CURL enabled."
            );
        }
    }
}
