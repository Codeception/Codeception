<?php

namespace Codeception\Command;

use Codeception\Lib\Generator\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;

/**
 * Creates default config, tests directory and sample suites for current project. Use this command to start building a test suite.
 *
 * By default it will create 3 suites **acceptance**, **functional**, and **unit**. To customize run this command with `--customize` option.
 *
 * For Codeception 1.x compatible setup run bootstrap in `--compat` option.
 *
 * * `codecept bootstrap` - creates `tests` dir and `codeception.yml` in current dir.
 * * `codecept bootstrap --customize` - set manually actors and suite names during setup
 * * `codecept bootstrap --compat` - prepare Codeception 1.x setup with Guy classes.
 * * `codecept bootstrap --namespace Frontend` - creates tests, and use `Frontend` namespace for actor classes and helpers.
 * * `codecept bootstrap --actor Wizard` - sets actor as Wizard, to have `TestWizard` actor in tests.
 * * `codecept bootstrap path/to/the/project` - provide different path to a project, where tests should be placed
 *
 */
class Bootstrap extends Command
{
    // defaults
    protected $namespace = '';
    protected $actorSuffix = 'Tester';
    protected $helperDir = 'tests/_support';
    protected $logDir = 'tests/_output';
    protected $dataDir = 'tests/_data';

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('path', InputArgument::OPTIONAL, 'custom installation path', '.'),
            new InputOption('namespace', 'ns', InputOption::VALUE_OPTIONAL, 'Namespace to add for actor classes and helpers'),
            new InputOption('actor', 'a', InputOption::VALUE_OPTIONAL, 'Custom actor instead of Guy'),
            new InputOption('compat', null, InputOption::VALUE_NONE, 'Codeception 1.x compatible setup'),
            new InputOption('customize', null, InputOption::VALUE_NONE, 'Customize suite and actors creation')
        ]);
    }

    public function getDescription()
    {
        return "Creates default test suites and generates all requires files";
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->namespace = rtrim($input->getOption('namespace'), '\\');

        if ($input->getOption('actor')) {
            $this->actorSuffix = $input->getOption('actor');
        }

        $path = $input->getArgument('path');

        if (!is_dir($path)) {
            $output->writeln("<error>\nDirectory '$path' does not exist\n</error>");
            return;
        }

        $realpath = realpath($path);
        chdir($path);

        if (file_exists('codeception.yml')) {
            $output->writeln("<error>\nProject is already initialized in '$path'\n</error>");
            return;
        }

        $output->writeln(
            "<fg=white;bg=magenta>Initializing Codeception in " . $realpath . "</fg=white;bg=magenta>\n"
        );
        
        if ($input->getOption('compat')) {
            $this->compatibilitySetup($output);
        } elseif ($input->getOption('customize')) {
            $this->customize($output);
        } else {
            $this->setup($output);
        }

        file_put_contents('tests/_bootstrap.php', "<?php\n// This is global bootstrap for autoloading\n");
        $output->writeln("tests/_bootstrap.php written <- global bootstrap file");

        $output->writeln("<info>Building initial {$this->actorSuffix} classes</info>");
        $this->getApplication()->find('build')->run(
            new ArrayInput(array('command' => 'build')),
            $output
        );

        $output->writeln("<info>\nBootstrap is done. Check out " . $realpath . "/tests directory</info>");
    }

    public function createGlobalConfig()
    {
        $basicConfig = array(
            'actor' => $this->actorSuffix,
            'paths'    => array(
                'tests'   => 'tests',
                'log'     => $this->logDir,
                'data'    => $this->dataDir,
                'helpers' => $this->helperDir
            ),
            'settings' => array(
                'bootstrap'    => '_bootstrap.php',
                'colors'       => (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'),
                'memory_limit' => '1024M'
            ),
            'modules'  => array(
                'config' => array(
                    'Db' => array(
                        'dsn'      => '',
                        'user'     => '',
                        'password' => '',
                        'dump'     => 'tests/_data/dump.sql'
                    )
                )
            )
        );

        $str = Yaml::dump($basicConfig, 4);
        if ($this->namespace) {
            $str = "namespace: {$this->namespace} \n" . $str;
        }
        file_put_contents('codeception.yml', $str);
    }

    protected function createFunctionalSuite($actor = 'Functional')
    {
        $suiteConfig = array(
            'class_name' => $actor.$this->actorSuffix,
            'modules'    => array('enabled' => array('Filesystem', $actor.'Helper')),
        );

        $str  = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for functional (integration) tests.\n";
        $str .= "# emulate web requests and make application process them.\n";
        $str .= "# Include one of framework modules (Symfony2, Yii2, Laravel5) to use it.\n\n";
        $str .= Yaml::dump($suiteConfig, 2);
        $this->createSuite('functional', $actor, $str);
    }

    protected function createAcceptanceSuite($actor = 'Acceptance')
    {
        $suiteConfig = array(
            'class_name' => $actor.$this->actorSuffix,
            'modules'    => array(
                'enabled' => array('PhpBrowser', $actor . 'Helper'),
                'config'  => array(
                    'PhpBrowser' => array(
                        'url' => 'http://localhost/myapp/'
                    ),
                )
            ),
        );

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for acceptance tests.\n";
        $str .= "# perform tests in browser using the WebDriver or PhpBrowser.\n";
        $str .= "# If you need both WebDriver and PHPBrowser tests - create a separate suite.\n\n";

        $str .= Yaml::dump($suiteConfig, 5);
        $this->createSuite('acceptance', $actor, $str);
    }

    protected function createUnitSuite($actor = 'Unit')
    {
        $suiteConfig = array(
            'class_name' => $actor.$this->actorSuffix,
            'modules'    => array('enabled' => array('Asserts', $actor . 'Helper')),
        );

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for unit (internal) tests.\n";
        $str .= Yaml::dump($suiteConfig, 2);
        
        $this->createSuite('unit', $actor, $str);
    }


    protected function createSuite($suite, $actor, $config)
    {
        @mkdir("tests/$suite");
        file_put_contents(
            "tests/$suite/_bootstrap.php",
            "<?php\n// Here you can initialize variables that will be available to your tests\n"
        );
        file_put_contents(
            $this->helperDir.DIRECTORY_SEPARATOR.$actor.'Helper.php',
            (new Helper($actor, $this->namespace))->produce()
        );
        file_put_contents("tests/$suite.suite.yml", $config);
    }

    /**
     * Performs Codeception 1.x compatible setup using with Guy classes
     */
    protected function compatibilitySetup(OutputInterface $output)
    {
        $this->actorSuffix = 'Guy';

        $this->logDir = 'tests/_log';
        $this->helperDir = 'tests/_helpers';

        $this->createGlobalConfig();
        $output->writeln("File codeception.yml created       <- global configuration");

        $this->createDirs();

        $this->createUnitSuite('Code');
        $output->writeln("tests/unit created                 <- unit tests");
        $output->writeln("tests/unit.suite.yml written       <- unit tests suite configuration");
        $this->createFunctionalSuite('Test');
        $output->writeln("tests/functional created           <- functional tests");
        $output->writeln("tests/functional.suite.yml written <- functional tests suite configuration");
        $this->createAcceptanceSuite('Web');
        $output->writeln("tests/acceptance created           <- acceptance tests");
        $output->writeln("tests/acceptance.suite.yml written <- acceptance tests suite configuration");

        if (file_exists('.gitignore')) {
            file_put_contents('tests/_log/.gitignore', '');
            file_put_contents('.gitignore', file_get_contents('.gitignore') . "\ntests/_log/*");
            $output->writeln("tests/_log was added to .gitignore");
        }

    }

    /**
     * @param OutputInterface $output
     */
    protected function setup(OutputInterface $output)
    {
        $this->createGlobalConfig();
        $output->writeln("File codeception.yml created       <- global configuration");

        $this->createDirs();
        $this->createUnitSuite();
        $output->writeln("tests/unit created                 <- unit tests");
        $output->writeln("tests/unit.suite.yml written       <- unit tests suite configuration");
        $this->createFunctionalSuite();
        $output->writeln("tests/functional created           <- functional tests");
        $output->writeln("tests/functional.suite.yml written <- functional tests suite configuration");
        $this->createAcceptanceSuite();
        $output->writeln("tests/acceptance created           <- acceptance tests");
        $output->writeln("tests/acceptance.suite.yml written <- acceptance tests suite configuration");

        if (file_exists('.gitignore')) {
            file_put_contents('tests/_output/.gitignore', '');
            file_put_contents('.gitignore', file_get_contents('.gitignore') . "\ntests/_output/*");
            $output->writeln("tests/_output was added to .gitignore");
        }
    }

    protected function customize(OutputInterface $output)
    {
        $output->writeln("Welcome to Customization Wizard");
        $dialog = $this->getHelperSet()->get('dialog');
        /** @var $dialog DialogHelper  **/
        $output->writeln("<comment>================================</comment>");
        $output->writeln("<comment> Configuring Actor </comment>\n");
        $this->actorSuffix = $dialog->ask($output,
            "<question> Enter default actor name </question> Proposed: <info>Tester</info>; Formerly: Guy\n",
            'Tester'
        );              

        $output->writeln("Basic Actor is set to: <info>{$this->actorSuffix}</info>");

        $this->createGlobalConfig();
        $output->writeln("File codeception.yml created <- global configuration");

        $this->createDirs();

        $output->writeln("\n<comment>================================</comment>");
        $output->writeln("<comment> Creating Suites </comment>\n");

        while ($suite = lcfirst($dialog->ask($output, "<question> Enter suite name (and its actor name if it differs from suite)</question> Enter to finish\n"))) {
            $suiteInput = explode(' ', $suite);
            if (isset($suiteInput[1])) {
                $suite = $suiteInput[0];
                $actor = ucfirst($suiteInput[1]);
            } else {
                $actor = ucfirst($suite);
            }
            $config = [
                'class_name' => $actor . $this->actorSuffix,
                'modules'    => ['enabled' => [$actor . 'Helper']]
            ];
            $this->createSuite($suite, $actor, Yaml::dump($config));
            $output->writeln("Suite <info>$suite</info> with actor <info>$actor{$this->actorSuffix}</info> and helper <info>{$actor}Helper</info> created");
            $output->writeln("\n<comment>================================</comment>");
        }
    }

    protected function createDirs()
    {
        @mkdir('tests');
        @mkdir($this->logDir);
        @mkdir($this->dataDir);
        @mkdir($this->helperDir);
        file_put_contents($this->dataDir . '/dump.sql', '/* Replace this file with actual dump of your database */');
    }

}
