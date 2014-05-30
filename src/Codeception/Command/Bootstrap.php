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
 * You will be asked to choose one of the actors that will be used in tests. To skip this question run bootstrap with `--silent` option.
 *
 * `codecept bootstrap` creates `tests` dir and `codeception.yml` in current dir.
 * `codecept bootstrap --namespace Frontend` - creates tests, and use `Frontend` namespace for actor classes and helpers.
 * `codecept bootstrap --actor Wizard` - sets actor as Wizard, to have `TestWizard` actor in tests.
 * `codecept bootstrap path/to/the/project` - provide different path to a project, where tests should be placed
 *
 */
class Bootstrap extends Command
{
    protected $namespace = '';
    protected $actor = 'Tester';

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('path', InputArgument::OPTIONAL, 'custom installation path', '.'),
            new InputOption('namespace', 'ns', InputOption::VALUE_OPTIONAL, 'Namespace to add for actor classes and helpers'),
            new InputOption('actor', 'a', InputOption::VALUE_OPTIONAL, 'Custom actor instead of Guy'),
            new InputOption('silent', null, InputOption::VALUE_NONE, 'Don\'t ask stupid questions')
        ]);
    }

    public function getDescription()
    {
        return "Creates default test suites and generates all requires file";
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->namespace = rtrim($input->getOption('namespace'), '\\');

        if ($input->getOption('actor')) {
            $this->actor = $input->getOption('actor');
        }

        $path = $input->getArgument('path');

        if (!is_dir($path)) {
            $output->writeln("<error>\nDirectory '$path' does not exists\n</error>");
            return;
        }

        chdir($path);

        if (file_exists('codeception.yml')) {
            $output->writeln("<error>\nProject already initialized at '$path'\n</error>");
            return;
        }

        $this->createGlobalConfig();
        $output->writeln(
            "<fg=white;bg=magenta>\nInitializing Codeception in " . realpath($path) . "\n</fg=white;bg=magenta>"
        );
        $output->writeln("File codeception.yml created <- global configuration");

        @mkdir('tests');
        @mkdir('tests/functional');
        @mkdir('tests/unit');
        @mkdir('tests/acceptance');
        @mkdir('tests/_helpers');
        @mkdir('tests/_log');
        @mkdir('tests/_data');

        $output->writeln("tests/unit created <- unit tests");
        $output->writeln("tests/functional created <- functional tests");
        $output->writeln("tests/acceptance created <- acceptance tests");

        file_put_contents('tests/_data/dump.sql', '/* Replace this file with actual dump of your database */');

        $this->createUnitSuite();
        $output->writeln("tests/unit.suite.yml written <- unit tests suite configuration");
        $this->createFunctionalSuite();
        $output->writeln("tests/functional.suite.yml written <- functional tests suite configuration");
        $this->createAcceptanceSuite();
        $output->writeln("tests/acceptance.suite.yml written <- acceptance tests suite configuration");

        file_put_contents('tests/_bootstrap.php', "<?php\n// This is global bootstrap for autoloading \n");
        $output->writeln("tests/_bootstrap.php written <- global bootstrap file");

        $output->writeln("<info>Building initial {$this->actor} classes</info>");
        $this->getApplication()->find('build')->run(
            new ArrayInput(array('command' => 'build')),
            $output
        );
        if (file_exists('.gitignore')) {
            file_put_contents('tests/_log/.gitignore', '');
            file_put_contents('.gitignore', file_get_contents('.gitignore') . "\ntests/_log/*");
            $output->writeln("tests/_log was added to .gitignore");
        }

        $output->writeln("<info>\nBootstrap is done. Check out " . realpath($path) . "/tests directory</info>");
    }

    public function createGlobalConfig()
    {
        $basicConfig = array(
            'actor' => $this->actor,
            'paths'    => array(
                'tests'   => 'tests',
                'log'     => 'tests/_log',
                'data'    => 'tests/_data',
                'helpers' => 'tests/_helpers'
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
            'class_name' => $actor.$this->actor,
            'modules'    => array('enabled' => array('Filesystem', $actor.'Helper')),
        );

        $str  = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for functional (integration) tests.\n";
        $str .= "# emulate web requests and make application process them.\n";
        $str .= "# Include one of framework modules (Symfony2, Yii2, Laravel4) to use it.\n\n";
        $str .= Yaml::dump($suiteConfig, 2);

        file_put_contents(
            'tests/_helpers/'.$actor.'Helper.php',
            (new Helper($actor, $this->namespace))->produce()
        );

        file_put_contents(
            'tests/functional/_bootstrap.php',
            "<?php\n// Here you can initialize variables that will be available to your tests\n"
        );
        file_put_contents('tests/functional.suite.yml', $str);
    }

    protected function createAcceptanceSuite($actor = 'Acceptance')
    {
        $suiteConfig = array(
            'class_name' => $actor.$this->actor,
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

        file_put_contents(
            'tests/acceptance/_bootstrap.php',
            "<?php\n// Here you can initialize variables that will be available to your tests\n"
        );
        file_put_contents(
            'tests/_helpers/AcceptanceHelper.php',
            (new Helper('Acceptance', $this->namespace))->produce()
        );
        file_put_contents('tests/acceptance.suite.yml', $str);
    }

    protected function createUnitSuite($actor = 'Unit')
    {
        // CodeGuy
        $suiteConfig = array(
            'class_name' => 'Unit'.$this->actor,
            'modules'    => array('enabled' => array('Asserts', 'UnitHelper')),
        );

        $str = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for unit (internal) tests.\n";
        $str .= Yaml::dump($suiteConfig, 2);

        file_put_contents(
            'tests/unit/_bootstrap.php',
            "<?php\n// Here you can initialize variables that will be available to your tests\n"
        );
        file_put_contents(
            'tests/_helpers/UnitHelper.php',
            (new Helper('Unit', $this->namespace))->produce()
        );
        file_put_contents('tests/unit.suite.yml', $str);
    }

    protected function setup()
    {

    }
}
