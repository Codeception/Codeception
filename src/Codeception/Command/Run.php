<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Codecept;
use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Codeception\Exception\ParseException;
use Exception;
use InvalidArgumentException;
use RuntimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException as SymfonyConsoleInvalidArgumentException;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function array_flip;
use function array_intersect_key;
use function array_merge;
use function count;
use function explode;
use function extension_loaded;
use function getcwd;
use function implode;
use function in_array;
use function preg_match;
use function preg_replace;
use function rtrim;
use function sprintf;
use function str_contains;
use function str_replace;
use function str_starts_with;
use function strpos;
use function strtolower;
use function substr;
use function substr_replace;

/**
 * Executes tests.
 *
 * Usage:
 *
 * * `codecept run acceptance`: run all acceptance tests
 * * `codecept run tests/acceptance/MyCest.php`: run only MyCest
 * * `codecept run acceptance MyCest`: same as above
 * * `codecept run acceptance MyCest:myTestInIt`: run one test from a Cest
 * * `codecept run acceptance MyCest:myTestInIt#1`: run one example or data provider item by number
 * * `codecept run acceptance MyCest:myTestInIt#1-3`: run a range of examples or data provider items
 * * `codecept run acceptance MyCest:myTestInIt@name.*`: run data provider items with matching names
 * * `codecept run acceptance checkout.feature`: run feature-file
 * * `codecept run acceptance -g slow`: run tests from *slow* group
 * * `codecept run unit,functional`: run only unit and functional suites
 *
 * Verbosity modes:
 *
 * * `codecept run -v`:
 * * `codecept run --steps`: print step-by-step execution
 * * `codecept run -vv`: print steps and debug information
 * * `codecept run --debug`: alias for `-vv`
 * * `codecept run -vvv`: print Codeception-internal debug information
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
 *
 * Run with specific extension
 *
 * * `codecept run --ext Recorder` run with Recorder extension enabled
 * * `codecept run --ext DotReporter` run with DotReporter printer
 * * `codecept run --ext "My\Custom\Extension"` run with an extension loaded by class name
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
 *  --phpunit-xml         Generate PhpUnit XML Log (default: "phpunit-report.xml")
 *  --no-redirect         Do not redirect to Composer-installed version in vendor/codeception
 *  --colors              Use colors in output
 *  --no-colors           Force no colors in output (useful to override config file)
 *  --silent              Only outputs suite names and final results. Almost the same as `--quiet`
 *  --steps               Show steps in output
 *  --debug (-d)          Alias for `-vv`
 *  --bootstrap           Execute bootstrap script before the test
 *  --coverage            Run with code coverage (default: "coverage.serialized")
 *  --coverage-html       Generate CodeCoverage HTML report in path (default: "coverage")
 *  --coverage-xml        Generate CodeCoverage XML report in file (default: "coverage.xml")
 *  --coverage-text       Generate CodeCoverage text report in file (default: "coverage.txt")
 *  --coverage-phpunit    Generate CodeCoverage PHPUnit report in file (default: "coverage-phpunit")
 *  --coverage-cobertura  Generate CodeCoverage Cobertura report in file (default: "coverage-cobertura")
 *  --no-exit             Don't finish with exit code
 *  --group (-g)          Groups of tests to be executed (multiple values allowed)
 *  --skip (-s)           Skip selected suites (multiple values allowed)
 *  --skip-group (-x)     Skip selected groups (multiple values allowed)
 *  --env                 Run tests in selected environments. (multiple values allowed, environments can be merged with ',')
 *  --fail-fast (-f)      Stop after nth failure (defaults to 1)
 *  --no-rebuild          Do not rebuild actor classes on start
 *  --help (-h)           Display this help message.
 *  --quiet (-q)          Do not output any message. Almost the same as `--silent`
 *  --verbose (-v|vv|vvv) Increase the verbosity of messages: `v` for normal output, `vv` for steps and debug, `vvv` for Codeception-internal debug
 *  --version (-V)        Display this application version.
 *  --ansi                Force ANSI output.
 *  --no-ansi             Disable ANSI output.
 *  --no-interaction (-n) Do not ask any interactive question.
 *  --seed                Use the given seed for shuffling tests
 * ```
 *
 */
class Run extends Command
{
    use Shared\ConfigTrait;

    protected ?Codecept $codecept = null;

    /**
     * @var int Executed suites
     */
    protected int $executed = 0;

    protected array $options = [];

    protected ?OutputInterface $output = null;

    /**
     * Sets Run arguments
     *
     * @throws SymfonyConsoleInvalidArgumentException
     */
    protected function configure(): void
    {
        $this->setDefinition([
            new InputArgument('suite', InputArgument::OPTIONAL, 'suite to be tested'),
            new InputArgument('test', InputArgument::OPTIONAL, 'test to be run'),
            new InputOption('override', 'o', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Override config values'),
            new InputOption('ext', 'e', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Run with extension enabled'),
            new InputOption('report', '', InputOption::VALUE_NONE, 'Show output in compact style'),
            new InputOption('html', '', InputOption::VALUE_OPTIONAL, 'Generate html with results', 'report.html'),
            new InputOption('xml', '', InputOption::VALUE_OPTIONAL, 'Generate JUnit XML Log', 'report.xml'),
            new InputOption('phpunit-xml', '', InputOption::VALUE_OPTIONAL, 'Generate PhpUnit XML Log', 'phpunit-report.xml'),
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
            new InputOption('shard', '', InputOption::VALUE_REQUIRED, 'Execute subset of tests to run tests on different machine. To split tests on 3 machines to run with shards: 1/3, 2/3, 3/3'),
            new InputOption('filter', '', InputOption::VALUE_REQUIRED, 'Filter tests by name'),
            new InputOption('grep', '', InputOption::VALUE_REQUIRED, 'Filter tests by name (alias to --filter)'),
            new InputOption('bootstrap', '', InputOption::VALUE_OPTIONAL, 'Execute custom PHP script before running tests. Path can be absolute or relative to current working directory', false),
            new InputOption('no-redirect', '', InputOption::VALUE_NONE, 'Do not redirect to Composer-installed version in vendor/codeception'),
            new InputOption(
                'coverage',
                '',
                InputOption::VALUE_OPTIONAL,
                'Run with code coverage'
            ),
            new InputOption(
                'coverage-html',
                '',
                InputOption::VALUE_OPTIONAL,
                'Generate CodeCoverage HTML report in path'
            ),
            new InputOption(
                'coverage-xml',
                '',
                InputOption::VALUE_OPTIONAL,
                'Generate CodeCoverage XML report in file'
            ),
            new InputOption(
                'coverage-text',
                '',
                InputOption::VALUE_OPTIONAL,
                'Generate CodeCoverage text report in file'
            ),
            new InputOption(
                'coverage-crap4j',
                '',
                InputOption::VALUE_OPTIONAL,
                'Generate CodeCoverage report in Crap4J XML format'
            ),
            new InputOption(
                'coverage-cobertura',
                '',
                InputOption::VALUE_OPTIONAL,
                'Generate CodeCoverage report in Cobertura XML format'
            ),
            new InputOption(
                'coverage-phpunit',
                '',
                InputOption::VALUE_OPTIONAL,
                'Generate CodeCoverage PHPUnit report in path'
            ),
            new InputOption('no-exit', '', InputOption::VALUE_NONE, "Don't finish with exit code"),
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
            new InputOption('fail-fast', 'f', InputOption::VALUE_OPTIONAL, 'Stop after nth failure'),
            new InputOption('no-rebuild', '', InputOption::VALUE_NONE, 'Do not rebuild actor classes on start'),
            new InputOption(
                'seed',
                '',
                InputOption::VALUE_REQUIRED,
                'Define random seed for shuffle setting'
            ),
            new InputOption('no-artifacts', '', InputOption::VALUE_NONE, "Don't report about artifacts"),
        ]);

        parent::configure();
    }

    public function getDescription(): string
    {
        return 'Runs the test suites';
    }

    /**
     * Executes Run
     *
     * @throws ConfigurationException|ParseException
     */
    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->ensurePhpExtIsAvailable('CURL');
        $this->ensurePhpExtIsAvailable('mbstring');
        $this->options = $input->getOptions();
        $this->output = $output;

        if ($this->options['bootstrap']) {
            Configuration::loadBootstrap($this->options['bootstrap'], getcwd());
        }

        $config = $this->getGlobalConfig();
        $config = $this->addRuntimeOptionsToCurrentConfig($config);

        if (!$this->options['colors']) {
            $this->options['colors'] = $config['settings']['colors'];
        }

        if (!$this->options['silent']) {
            $this->output->writeln(
                Codecept::versionString() . ' https://helpukrainewin.org'
            );

            if ($this->options['seed']) {
                $this->output->writeln(
                    "Running with seed: <info>" . $this->options['seed'] . "</info>\n"
                );
            }
        }
        if ($this->options['debug']) {
            $this->output->setVerbosity(OutputInterface::VERBOSITY_VERY_VERBOSE);
        }

        $userOptions = array_intersect_key($this->options, array_flip($this->passedOptionKeys($input)));
        $userOptions = array_merge(
            $userOptions,
            $this->booleanOptions($input, [
                'xml' => 'report.xml',
                'phpunit-xml' => 'phpunit-report.xml',
                'html' => 'report.html',
                'coverage' => 'coverage.serialized',
                'coverage-xml' => 'coverage.xml',
                'coverage-html' => 'coverage',
                'coverage-text' => 'coverage.txt',
                'coverage-crap4j' => 'crap4j.xml',
                'coverage-cobertura' => 'cobertura.xml',
                'coverage-phpunit' => 'coverage-phpunit'])
        );
        $userOptions['verbosity'] = $this->output->getVerbosity();
        $userOptions['interactive'] = !$input->hasParameterOption(['--no-interaction', '-n']);
        $userOptions['ansi'] = (!$input->hasParameterOption('--no-ansi') xor $input->hasParameterOption('ansi'));

        $userOptions['seed'] = $this->options['seed'] ? (int)$this->options['seed'] : rand();
        if ($this->options['no-colors'] || !$userOptions['ansi']) {
            $userOptions['colors'] = false;
        }
        if ($this->options['group']) {
            $userOptions['groups'] = $this->options['group'];
        }
        if ($this->options['skip-group']) {
            $userOptions['excludeGroups'] = $this->options['skip-group'];
        }
        if ($this->options['coverage-xml'] || $this->options['coverage-html'] || $this->options['coverage-text'] || $this->options['coverage-crap4j'] || $this->options['coverage-phpunit']) {
            $this->options['coverage'] = true;
        }
        if (!$userOptions['ansi'] && $input->getOption('colors')) {
            $userOptions['colors'] = true; // turn on colors even in non-ansi mode if strictly passed
        }
        // array key will exist if fail-fast option is used
        if (array_key_exists('fail-fast', $userOptions)) {
            $userOptions['fail-fast'] = (int)$this->options['fail-fast'] ?: 1;
        }

        $suite = $input->getArgument('suite');
        $test = $input->getArgument('test');

        if ($this->options['group']) {
            $this->output->writeln(sprintf("[Groups] <info>%s</info> ", implode(', ', $this->options['group'])));
        }
        if ($input->getArgument('test')) {
            $this->options['steps'] = true;
        }

        if (!$test) {
            // Check if suite is given and is in an included path
            if (!empty($suite) && !empty($config['include'])) {
                $isIncludeTest = false;
                // Remember original projectDir
                $projectDir = Configuration::projectDir();

                foreach ($config['include'] as $include) {
                    // Find if the suite begins with an include path
                    if (str_starts_with($suite, (string)$include)) {
                        // Use include config
                        $config = Configuration::config($projectDir . $include);
                        $config = $this->addRuntimeOptionsToCurrentConfig($config);

                        if (!empty($this->options['override'])) {
                            $config = $this->overrideConfig($this->options['override']);
                        }

                        if (!isset($config['paths']['tests'])) {
                            throw new RuntimeException(
                                sprintf("Included '%s' has no tests path configured", $include)
                            );
                        }

                        $testsPath = $include . DIRECTORY_SEPARATOR . $config['paths']['tests'];

                        try {
                            [, $suite, $test] = $this->matchTestFromFilename($suite, $testsPath);
                            $isIncludeTest = true;
                        } catch (InvalidArgumentException) {
                            // Incorrect include match, continue trying to find one
                            continue;
                        }
                    } else {
                        $result = $this->matchSingleTest($suite, $config);
                        if ($result) {
                            [, $suite, $test] = $result;
                        }
                    }
                }

                // Restore main config
                if (!$isIncludeTest) {
                    $config = $this->addRuntimeOptionsToCurrentConfig(
                        Configuration::config($projectDir)
                    );
                }
            } elseif (!empty($suite)) {
                $result = $this->matchSingleTest($suite, $config);
                if ($result) {
                    [, $suite, $test] = $result;
                }
            }
        }

        $filter = $input->getOption('filter') ?? $input->getOption('grep') ?? null;
        if ($test) {
            $userOptions['filter'] = $this->matchFilteredTestName($test);
        } elseif (
            $suite
            && !$this->isWildcardSuiteName($suite)
            && !$this->isSuiteInMultiApplication($suite)
        ) {
            $userOptions['filter'] = $this->matchFilteredTestName($suite);
        }

        if (isset($userOptions['filter']) && $filter) {
            throw new InvalidOptionException("--filter and --grep can't be used with a test name");
        } elseif ($filter) {
            $userOptions['filter'] = $filter;
        }

        if ($this->options['shard']) {
            $this->output->writeln(
                "[Shard ${userOptions['shard']}] <info>Running subset of tests</info>"
            );
            // disable shuffle for sharding
            $config['settings']['shuffle'] = false;
        }

        if (!$this->options['silent'] && $config['settings']['shuffle']) {
            $this->output->writeln(
                "[Seed] <info>" . $userOptions['seed'] . "</info>"
            );
        }

        $this->codecept = new Codecept($userOptions);

        if ($suite && $test) {
            $this->codecept->run($suite, $test, $config);
        }

        // Run all tests of given suite or all suites
        if (!$test) {
            $didPassCliSuite = !empty($suite);

            $rawSuites = $didPassCliSuite ? explode(',', $suite) : Configuration::suites();

            /** @var string[] $mainAppSuites */
            $mainAppSuites = [];

            /** @var array<string,string> $appSpecificSuites */
            $appSpecificSuites = [];

            /** @var string[] $wildcardSuites */
            $wildcardSuites = [];

            foreach ($rawSuites as $rawSuite) {
                if ($this->isWildcardSuiteName($rawSuite)) {
                    $wildcardSuites[] = explode('*::', $rawSuite)[1];
                    continue;
                }
                if ($this->isSuiteInMultiApplication($rawSuite)) {
                    $appAndSuite = explode('::', $rawSuite);
                    $appSpecificSuites[$appAndSuite[0]][] = $appAndSuite[1];
                    continue;
                }
                $mainAppSuites[] = $rawSuite;
            }

            if ([] !== $mainAppSuites) {
                $this->executed = $this->runSuites($mainAppSuites, $this->options['skip']);
            }

            if (!empty($wildcardSuites) && ! empty($appSpecificSuites)) {
                $this->output->writeLn('<error>Wildcard options can not be combined with specific suites of included apps.</error>');
                return 2;
            }

            if (
                !empty($config['include'])
                && (!$didPassCliSuite || !empty($wildcardSuites) || !empty($appSpecificSuites))
            ) {
                $currentDir = Configuration::projectDir();
                $includedApps = $config['include'];

                if (!empty($appSpecificSuites)) {
                    $includedApps = array_intersect($includedApps, array_keys($appSpecificSuites));
                }

                $this->runIncludedSuites(
                    $includedApps,
                    $currentDir,
                    $appSpecificSuites,
                    $wildcardSuites
                );
            }

            if ($this->executed === 0) {
                throw new RuntimeException(
                    sprintf("Suite '%s' could not be found", implode(', ', $rawSuites))
                );
            }
        }

        $this->codecept->printResult();

        if ($this->options['shard']) {
            $this->output->writeln(
                "[Shard ${userOptions['shard']}] <info>Merge this result with other shards to see the complete report</info>"
            );
        }

        if (!$input->getOption('no-exit') && !$this->codecept->getResultAggregator()->wasSuccessfulIgnoringWarnings()) {
            exit(1);
        }

        return 0;
    }

    protected function matchSingleTest($suite, $config): ?array
    {
        // Workaround when codeception.yml is inside tests directory and tests path is set to "."
        // @see https://github.com/Codeception/Codeception/issues/4432
        if (isset($config['paths']['tests']) && $config['paths']['tests'] === '.' && !preg_match('#^\.[/\\\]#', $suite)) {
            $suite = './' . $suite;
        }

        // running a single test when suite has a configured path
        if (isset($config['suites'])) {
            foreach ($config['suites'] as $s => $suiteConfig) {
                if (!isset($suiteConfig['path'])) {
                    continue;
                }
                $testsPath = $config['paths']['tests'] . DIRECTORY_SEPARATOR . $suiteConfig['path'];
                if ($suiteConfig['path'] === '.') {
                    $testsPath = $config['paths']['tests'];
                }
                if (preg_match("#^{$testsPath}/(.*?)$#", $suite, $matches)) {
                    $matches[2] = $matches[1];
                    $matches[1] = $s;
                    return $matches;
                }
            }
        }

        if (!Configuration::isEmpty()) {
            // Run single test without included tests
            if (str_starts_with($suite, (string)$config['paths']['tests'])) {
                return $this->matchTestFromFilename($suite, $config['paths']['tests']);
            }

            // Run single test from working directory
            $realTestDir = (string)realpath(Configuration::testsDir());
            $cwd = (string)getcwd();
            if (str_starts_with($realTestDir, $cwd)) {
                $file = $suite;
                if (str_contains($file, ':')) {
                    [$file] = explode(':', $suite, -1);
                }
                $realPath = $cwd . DIRECTORY_SEPARATOR . $file;
                if (file_exists($realPath) && str_starts_with($realPath, $realTestDir)) {
                    //only match test if file is in tests directory
                    return $this->matchTestFromFilename(
                        $cwd . DIRECTORY_SEPARATOR . $suite,
                        $realTestDir
                    );
                }
            }
        }

        return null;
    }

    /**
     * Runs included suites recursively
     *
     * @param string[] $suites
     * @param array<string,string[]> $filterAppSuites An array keyed by included app name where values are suite names to run.
     * @param string[] $filterSuitesByWildcard A list of suite names (applies to all included apps)
     * @throws ConfigurationException
     */
    protected function runIncludedSuites(
        array $suites,
        string $parentDir,
        array $filterAppSuites = [],
        array $filterSuitesByWildcard = [],
    ) {
        $defaultConfig = Configuration::config();
        $absolutePath = Configuration::projectDir();

        foreach ($suites as $relativePath) {
            $currentDir = rtrim($parentDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $relativePath;
            $config = Configuration::config($currentDir);

            if (!empty($defaultConfig['groups'])) {
                $groups = array_map(fn ($g) => $absolutePath . $g, $defaultConfig['groups']);
                Configuration::append(['groups' => $groups]);
            }

            $suites = Configuration::suites();

            if (!empty($filterSuitesByWildcard)) {
                $suites = array_intersect($suites, $filterSuitesByWildcard);
            }

            if (isset($filterAppSuites[$relativePath])) {
                $suites = array_intersect($suites, $filterAppSuites[$relativePath]);
            }

            $namespace = $this->currentNamespace();
            $this->output->writeln(
                "\n<fg=white;bg=magenta>\n[{$namespace}]: tests from {$currentDir}\n</fg=white;bg=magenta>"
            );

            $this->executed += $this->runSuites($suites, $this->options['skip']);
            if (!empty($config['include'])) {
                $this->runIncludedSuites($config['include'], $currentDir);
            }
        }
    }

    protected function currentNamespace(): string
    {
        $config = Configuration::config();
        if (!$config['namespace']) {
            throw new RuntimeException(
                "Can't include into runner suite without a namespace;\n"
                . "Please add `namespace` section into included codeception.yml file"
            );
        }

        return $config['namespace'];
    }

    /**
     * @param string[] $suites
     * @param string[] $skippedSuites
     * @return int Number of executed test suites
     */
    protected function runSuites(array $suites, array $skippedSuites = []): int
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
            ++$executed;
        }

        return $executed;
    }

    /**
     * @return string[]
     */
    protected function matchTestFromFilename(string $filename, string $testsPath): array
    {
        $filter = '';
        if (str_contains($filename, ':')) {
            if ((PHP_OS === 'Windows' || PHP_OS === 'WINNT') && $filename[1] === ':') {
                // match C:\...
                [$drive, $path, $filter] = explode(':', $filename, 3);
                $filename = $drive . ':' . $path;
            } else {
                [$filename, $filter] = explode(':', $filename, 2);
            }

            if ($filter !== '') {
                $filter = ':' . $filter;
            }
        }

        $testsPath = str_replace(['//', '\/', '\\'], '/', $testsPath);
        $filename = str_replace(['//', '\/', '\\'], '/', $filename);

        if (rtrim($filename, '/') === $testsPath) {
            //codecept run tests
            return ['', '', $filter];
        }
        $res = preg_match("#^{$testsPath}/(.*?)(?>/(.*))?$#", $filename, $matches);

        if (!$res) {
            throw new InvalidArgumentException("Test file can't be matched");
        }
        if (!isset($matches[2])) {
            $matches[2] = '';
        }
        if ($filter !== '') {
            $matches[2] .= $filter;
        }

        return $matches;
    }

    private function matchFilteredTestName(string &$path): ?string
    {
        $testParts = explode(':', $path, 2);
        if (count($testParts) > 1) {
            [$path, $filter] = $testParts;
            // use carat to signify start of string like in normal regex
            // phpunit --filter matches against the fully qualified method name, so tests actually begin with :
            $caratPos = strpos($filter, '^');
            if ($caratPos !== false) {
                $filter = substr_replace($filter, ':', $caratPos, 1);
            }
            return $filter;
        }

        return null;
    }

    /**
     * @return string[]
     */
    protected function passedOptionKeys(ArgvInput $input): array
    {
        $options = [];
        $request = (string)$input;
        $tokens = explode(' ', $request);
        foreach ($tokens as $token) {
            $token = preg_replace('#=.*#', '', $token); // strip = from options

            if (empty($token)) {
                continue;
            }

            if ($token == '--') {
                break; // there should be no options after ' -- ', only arguments
            }

            if (str_starts_with($token, '--')) {
                $options[] = substr($token, 2);
            } elseif ($token[0] === '-') {
                $shortOption = substr($token, 1);
                $options[] = $this->getDefinition()->getOptionForShortcut($shortOption)->getName();
            }
        }
        return $options;
    }

    /**
     * @return array<string, bool>
     */
    protected function booleanOptions(ArgvInput $input, array $options = []): array
    {
        $values = [];
        $request = (string)$input;
        foreach ($options as $option => $defaultValue) {
            if (strpos($request, sprintf('--%s', $option))) {
                $values[$option] = $input->getOption($option) ?: $defaultValue;
            } else {
                $values[$option] = false;
            }
        }

        return $values;
    }

    /**
     * @throws Exception
     */
    private function ensurePhpExtIsAvailable(string $ext): void
    {
        if (!extension_loaded(strtolower($ext))) {
            throw new Exception(
                "Codeception requires \"{$ext}\" extension installed to make tests run\n"
                . "If you are not sure, how to install \"{$ext}\", please refer to StackOverflow\n\n"
                . "Notice: PHP for Apache/Nginx and CLI can have different php.ini files.\n"
                . "Please make sure that your PHP you run from console has \"{$ext}\" enabled."
            );
        }
    }

    private function isWildcardSuiteName(string $suiteName): bool
    {
        return str_starts_with($suiteName, '*::');
    }

    private function isSuiteInMultiApplication(string $suiteName): bool
    {
        return str_contains($suiteName, '::');
    }

    private function addRuntimeOptionsToCurrentConfig(array $config): array
    {
        // update config from options
        if (count($this->options['override'])) {
            $config = $this->overrideConfig($this->options['override']);
        }
        // enable extensions
        if ($this->options['ext']) {
            $config = $this->enableExtensions($this->options['ext']);
        }

        return $config;
    }
}
