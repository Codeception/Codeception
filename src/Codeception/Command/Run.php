<?php
namespace Codeception\Command;

use Codeception\Codecept;
use Codeception\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Executes tests.
 *
 * Usage:
 *
 * * `codecept run acceptance`: run all acceptance tests
 * * `codecept run tests/acceptance/MyCept.php`: run only MyCept
 * * `codecept run acceptance MyCept`: same as above
 * * `codecept run acceptance MyCest:myTestInIt`: run one test from a Cest
 * * `codecept run acceptance checkout.feature`: run feature-file
 * * `codecept run acceptance -g slow`: run tests from *slow* group
 * * `codecept run unit,functional`: run only unit and functional suites
 *
 * Verbosity modes:
 *
 * * `codecept run -v`:
 * * `codecept run --steps`: print step-by-step execution
 * * `codecept run -vv`:
 * * `codecept run --debug`: print steps and debug information
 * * `codecept run -vvv`: print internal debug information
 *
 * Load config:
 *
 * * `codecept run -c path/to/another/config`: from another dir
 * * `codecept run -c another_config.yml`: from another config file
 *
 * Override config values:
 *
 * * `codecept run -o "settings: shuffle: true"`: enable shuffle
 * * `codecept run -o "settings: lint: false"`: disable linting
 * * `codecept run -o "reporters: report: \Custom\Reporter" --report`: use custom reporter
 *
 * Full reference:
 * ```
 * Arguments:
 *  suite                 suite to be tested
 *  test                  test to be run
 *
 * Options:
 *  -o, --override=OVERRIDE Override config values (multiple values allowed)
 *  --config (-c)         Use custom path for config
 *  --report              Show output in compact style
 *  --html                Generate html with results (default: "report.html")
 *  --xml                 Generate JUnit XML Log (default: "report.xml")
 *  --tap                 Generate Tap Log (default: "report.tap.log")
 *  --json                Generate Json Log (default: "report.json")
 *  --colors              Use colors in output
 *  --no-colors           Force no colors in output (useful to override config file)
 *  --silent              Only outputs suite names and final results
 *  --steps               Show steps in output
 *  --debug (-d)          Show debug and scenario output
 *  --coverage            Run with code coverage (default: "coverage.serialized")
 *  --coverage-html       Generate CodeCoverage HTML report in path (default: "coverage")
 *  --coverage-xml        Generate CodeCoverage XML report in file (default: "coverage.xml")
 *  --coverage-text       Generate CodeCoverage text report in file (default: "coverage.txt")
 *  --no-exit             Don't finish with exit code
 *  --group (-g)          Groups of tests to be executed (multiple values allowed)
 *  --skip (-s)           Skip selected suites (multiple values allowed)
 *  --skip-group (-x)     Skip selected groups (multiple values allowed)
 *  --env                 Run tests in selected environments. (multiple values allowed, environments can be merged with ',')
 *  --fail-fast (-f)      Stop after first failure
 *  --help (-h)           Display this help message.
 *  --quiet (-q)          Do not output any message.
 *  --verbose (-v|vv|vvv) Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug
 *  --version (-V)        Display this application version.
 *  --ansi                Force ANSI output.
 *  --no-ansi             Disable ANSI output.
 *  --no-interaction (-n) Do not ask any interactive question.
 * ```
 *
 */
class Run extends Command
{
    use Shared\Config;
    /**
     * @var Codecept
     */
    protected $codecept;

    /**
     * @var integer of executed suites
     */
    protected $executed = 0;

    /**
     * @var array of options (command run)
     */
    protected $options = [];

    /**
     * @var OutputInterface
     */
    protected $output;


    /**
     * Sets Run arguments
     * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
     */
    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('suite', InputArgument::OPTIONAL, 'suite to be tested'),
            new InputArgument('test', InputArgument::OPTIONAL, 'test to be run'),
            new InputOption('override', 'o', InputOption::VALUE_IS_ARRAY  | InputOption::VALUE_REQUIRED, 'Override config values'),
            new InputOption('report', '', InputOption::VALUE_NONE, 'Show output in compact style'),
            new InputOption('html', '', InputOption::VALUE_OPTIONAL, 'Generate html with results', 'report.html'),
            new InputOption('xml', '', InputOption::VALUE_OPTIONAL, 'Generate JUnit XML Log', 'report.xml'),
            new InputOption('tap', '', InputOption::VALUE_OPTIONAL, 'Generate Tap Log', 'report.tap.log'),
            new InputOption('json', '', InputOption::VALUE_OPTIONAL, 'Generate Json Log', 'report.json'),
            new InputOption('colors', '', InputOption::VALUE_NONE, 'Use colors in output'),
            new InputOption(
                'no-colors',
                '',
                InputOption::VALUE_NONE,
                'Force no colors in output (useful to override config file)'
            ),
            new InputOption('silent', '', InputOption::VALUE_NONE, 'Only outputs suite names and final results'),
            new InputOption('steps', '', InputOption::VALUE_NONE, 'Show steps in output'),
            new InputOption('debug', 'd', InputOption::VALUE_NONE, 'Show debug and scenario output'),
            new InputOption(
                'coverage',
                '',
                InputOption::VALUE_OPTIONAL,
                'Run with code coverage',
                'coverage.serialized'
            ),
            new InputOption(
                'coverage-html',
                '',
                InputOption::VALUE_OPTIONAL,
                'Generate CodeCoverage HTML report in path',
                'coverage'
            ),
            new InputOption(
                'coverage-xml',
                '',
                InputOption::VALUE_OPTIONAL,
                'Generate CodeCoverage XML report in file',
                'coverage.xml'
            ),
            new InputOption(
                'coverage-text',
                '',
                InputOption::VALUE_OPTIONAL,
                'Generate CodeCoverage text report in file',
                'coverage.txt'
            ),
            new InputOption(
                'coverage-crap4j',
                '',
                InputOption::VALUE_OPTIONAL,
                'Generate CodeCoverage report in Crap4J XML format',
                'crap4j.xml'
            ),
            new InputOption('no-exit', '', InputOption::VALUE_NONE, 'Don\'t finish with exit code'),
            new InputOption(
                'group',
                'g',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Groups of tests to be executed'
            ),
            new InputOption(
                'skip',
                's',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Skip selected suites'
            ),
            new InputOption(
                'skip-group',
                'x',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Skip selected groups'
            ),
            new InputOption(
                'env',
                '',
                InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED,
                'Run tests in selected environments.'
            ),
            new InputOption('fail-fast', 'f', InputOption::VALUE_NONE, 'Stop after first failure'),
            new InputOption('no-rebuild', '', InputOption::VALUE_NONE, 'Do not rebuild actor classes on start'),
        ]);

        parent::configure();
    }

    public function getDescription()
    {
        return 'Runs the test suites';
    }

    /**
     * Executes Run
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int|null|void
     * @throws \RuntimeException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->ensureCurlIsAvailable();
        $this->options = $input->getOptions();
        $this->output = $output;

        // load config
        $config = $this->getGlobalConfig($this->options['config']);

        // update config from options
        if (count($this->options['override'])) {
            $config = $this->overrideConfig($this->options['override']);
        }

        if (!$this->options['colors']) {
            $this->options['colors'] = $config['settings']['colors'];
        }
        if (!$this->options['silent']) {
            $this->output->writeln(
                Codecept::versionString() . "\nPowered by " . \PHPUnit_Runner_Version::getVersionString()
            );
        }
        if ($this->options['debug']) {
            $this->output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        }

        $userOptions = array_intersect_key($this->options, array_flip($this->passedOptionKeys($input)));
        $userOptions = array_merge(
            $userOptions,
            $this->booleanOptions($input, ['xml', 'html', 'json', 'tap', 'coverage', 'coverage-xml', 'coverage-html', 'coverage-crap4j'])
        );
        $userOptions['verbosity'] = $this->output->getVerbosity();
        $userOptions['interactive'] = !$input->hasParameterOption(['--no-interaction', '-n']);
        $userOptions['ansi'] = (!$input->hasParameterOption('--no-ansi') xor $input->hasParameterOption('ansi'));

        if ($this->options['no-colors'] || !$userOptions['ansi']) {
            $userOptions['colors'] = false;
        }
        if ($this->options['group']) {
            $userOptions['groups'] = $this->options['group'];
        }
        if ($this->options['skip-group']) {
            $userOptions['excludeGroups'] = $this->options['skip-group'];
        }
        if ($this->options['report']) {
            $userOptions['silent'] = true;
        }
        if ($this->options['coverage-xml'] or $this->options['coverage-html'] or $this->options['coverage-text'] or $this->options['coverage-crap4j']) {
            $this->options['coverage'] = true;
        }
        if (!$userOptions['ansi'] && $input->getOption('colors')) {
            $userOptions['colors'] = true; // turn on colors even in non-ansi mode if strictly passed
        }

        $suite = $input->getArgument('suite');
        $test = $input->getArgument('test');

        if (! Configuration::isEmpty() && ! $test && strpos($suite, $config['paths']['tests']) === 0) {
            list(, $suite, $test) = $this->matchTestFromFilename($suite, $config['paths']['tests']);
        }

        if ($this->options['group']) {
            $this->output->writeln(sprintf("[Groups] <info>%s</info> ", implode(', ', $this->options['group'])));
        }
        if ($input->getArgument('test')) {
            $this->options['steps'] = true;
        }

        if ($test) {
            $filter = $this->matchFilteredTestName($test);
            $userOptions['filter'] = $filter;
        }

        $this->codecept = new Codecept($userOptions);

        if ($suite and $test) {
            $this->codecept->run($suite, $test);
        }

        if (!$test) {
            $suites = $suite ? explode(',', $suite) : Configuration::suites();
            $this->executed = $this->runSuites($suites, $this->options['skip']);

            if (!empty($config['include']) and !$suite) {
                $current_dir = Configuration::projectDir();
                $suites += $config['include'];
                $this->runIncludedSuites($config['include'], $current_dir);
            }

            if ($this->executed === 0) {
                throw new \RuntimeException(
                    sprintf("Suite '%s' could not be found", implode(', ', $suites))
                );
            }
        }

        $this->codecept->printResult();

        if (!$input->getOption('no-exit')) {
            if (!$this->codecept->getResult()->wasSuccessful()) {
                exit(1);
            }
        }
    }

    /**
     * Runs included suites recursively
     *
     * @param array $suites
     * @param string $parent_dir
     */
    protected function runIncludedSuites($suites, $parent_dir)
    {
        foreach ($suites as $relativePath) {
            $current_dir = rtrim($parent_dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relativePath;
            $config = Configuration::config($current_dir);
            $suites = Configuration::suites();

            $namespace = $this->currentNamespace();
            $this->output->writeln(
                "\n<fg=white;bg=magenta>\n[$namespace]: tests from $current_dir\n</fg=white;bg=magenta>"
            );

            $this->executed += $this->runSuites($suites, $this->options['skip']);
            if (!empty($config['include'])) {
                $this->runIncludedSuites($config['include'], $current_dir);
            }
        }
    }


    protected function currentNamespace()
    {
        $config = Configuration::config();
        if (!$config['namespace']) {
            throw new \RuntimeException(
                "Can't include into runner suite without a namespace;\n"
                . "Please add `namespace` section into included codeception.yml file"
            );
        }

        return $config['namespace'];
    }

    protected function runSuites($suites, $skippedSuites = [])
    {
        $executed = 0;
        foreach ($suites as $suite) {
            if (in_array($suite, $skippedSuites)) {
                continue;
            }
            if (!in_array($suite, Configuration::suites())) {
                continue;
            }
            $this->codecept->run($suite);
            $executed++;
        }

        return $executed;
    }

    protected function matchTestFromFilename($filename, $tests_path)
    {
        $filename = str_replace(['//', '\/', '\\'], '/', $filename);
        $res = preg_match("~^$tests_path/(.*?)/(.*)$~", $filename, $matches);
        if (!$res) {
            throw new \InvalidArgumentException("Test file can't be matched");
        }

        return $matches;
    }

    private function matchFilteredTestName(&$path)
    {
        $test_parts = explode(':', $path, 2);
        if (count($test_parts) > 1) {
            list($path, $filter) = $test_parts;
            // use carat to signify start of string like in normal regex
            // phpunit --filter matches against the fully qualified method name, so tests actually begin with :
            $carat_pos = strpos($filter, '^');
            if ($carat_pos !== false) {
                $filter = substr_replace($filter, ':', $carat_pos, 1);
            }
            return $filter;
        }

        return null;
    }

    protected function passedOptionKeys(InputInterface $input)
    {
        $options = [];
        $request = (string)$input;
        $tokens = explode(' ', $request);
        foreach ($tokens as $token) {
            $token = preg_replace('~=.*~', '', $token); // strip = from options
            
            if (empty($token)) {
                continue;
            }
            
            if ($token == '--') {
                break; // there should be no options after ' -- ', only arguments
            }

            if (substr($token, 0, 2) === '--') {
                $options[] = substr($token, 2);
            } elseif ($token[0] === '-') {
                $shortOption = substr($token, 1);
                $options[] = $this->getDefinition()->getOptionForShortcut($shortOption)->getName();
            }
        }
        return $options;
    }

    protected function booleanOptions(InputInterface $input, $options = [])
    {
        $values = [];
        $request = (string)$input;
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
        if (!extension_loaded('curl')) {
            throw new \Exception(
                "Codeception requires CURL extension installed to make tests run\n"
                . "If you are not sure, how to install CURL, please refer to StackOverflow\n\n"
                . "Notice: PHP for Apache/Nginx and CLI can have different php.ini files.\n"
                . "Please make sure that your PHP you run from console has CURL enabled."
            );
        }
    }
}
