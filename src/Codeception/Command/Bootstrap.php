<?php
namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\DialogHelper;
use Symfony\Component\Yaml\Yaml;

class Bootstrap extends \Symfony\Component\Console\Command\Command
{

    public function getDescription()
    {
        return 'Initializes empty test suite and default configuration file';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {

        if (file_exists('codeception.yml')) {
            $output->writeln("<error>\nProject already initialized here\n</error>");
            return;
        }

        $basicConfig = array(
            'paths' => array(
                'tests' => 'tests',
                'log' => 'tests/_log',
                'data' => 'tests/_data',
                'helpers' => 'tests/_helpers'
            ),
            'settings' => array(
                'bootstrap' => '_bootstrap.php',
                'suite_class' => '\PHPUnit_Framework_TestSuite',
                'colors' => (strtoupper(substr(PHP_OS, 0, 3)) != 'WIN'),
                'memory_limit' => '1024M',
                'log' => true
            ),
            'modules' => array('config' => array(
              'Db' => array(
                'dsn' => '',
                'user' => '',
                'password' => '',
                'dump' => 'tests/_data/dump.sql'
              )
            )
            )
        );

        $str = Yaml::dump($basicConfig, 4);
        file_put_contents('codeception.yml', $str);

        $output->writeln("File codeception.yml written - global configuration");

        @mkdir('tests');
        @mkdir('tests/functional');
        @mkdir('tests/unit');
        @mkdir('tests/acceptance');
        @mkdir('tests/_helpers');
        @mkdir('tests/_log');
        @mkdir('tests/_data');

        $output->writeln("tests/unit created - unit tests");
        $output->writeln("tests/functional created - functional tests");
        $output->writeln("tests/acceptance created - acceptance tests");

        file_put_contents('tests/_data/dump.sql', '/* Replace this file with actual dump of your database */');

        file_put_contents('tests/unit/_bootstrap.php', "<?php\n// Here you can initialize variables that will for your tests\n");
        file_put_contents('tests/functional/_bootstrap.php', "<?php\n// Here you can initialize variables that will for your tests\n");
        file_put_contents('tests/acceptance/_bootstrap.php', "<?php\n// Here you can initialize variables that will for your tests\n");


        file_put_contents('tests/_helpers/CodeHelper.php', "<?php\nnamespace Codeception\\Module;\n\nrequire_once 'PHPUnit/Framework/Assert/Functions.php';\n\n// here you can define custom functions for CodeGuy \n\nclass CodeHelper extends \\Codeception\\Module\n{\n}\n");
        file_put_contents('tests/_helpers/TestHelper.php', "<?php\nnamespace Codeception\\Module;\n\nrequire_once 'PHPUnit/Framework/Assert/Functions.php';\n\n// here you can define custom functions for TestGuy \n\nclass TestHelper extends \\Codeception\\Module\n{\n}\n");
        file_put_contents('tests/_helpers/WebHelper.php', "<?php\nnamespace Codeception\\Module;\n\nrequire_once 'PHPUnit/Framework/Assert/Functions.php';\n\n// here you can define custom functions for WebGuy \n\nclass WebHelper extends \\Codeception\\Module\n{\n}\n");

//        file_put_contents('tests/unit/SampleSpec.php', "<?php\n\$I = new CodeGuy(\$scenario);\n\$I->wantTo('test a code specification');");
//        file_put_contents('tests/functional/SampleSpec.php', "<?php\n\$I = new TestGuy(\$scenario);\n\$I->wantTo('test an integration feature');");
//        file_put_contents('tests/acceptance/SampleSpec.php', "<?php\n\$I = new WebGuy(\$scenario);\n\$I->wantTo('test an application feature in a web browser');");

        // CodeGuy
        $conf = array(
            'class_name' => 'CodeGuy',
            'modules' => array('enabled' => array('Unit','CodeHelper')),

        );

        $firstline = $str  = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for unit (internal) tests.\n";
        $str .= Yaml::dump($conf, 2);

        file_put_contents('tests/unit.suite.yml', $str);
        $output->writeln("tests/unit.suite.yml written - unit tests suite configuration");


        // CodeGuy
        $suiteConfig = array(
            'class_name' => 'TestGuy',
            'modules' => array('enabled' => array('Filesystem', 'TestHelper')),
        );

        $str = $firstline;
        $str .= "# suite for functional (integration) tests.\n";
        $str .= "# emulate web requests and make application process them.\n";
        $str .= "# (tip: better to use with frameworks).\n\n";
        $str .= Yaml::dump($suiteConfig, 2);

        file_put_contents('tests/functional.suite.yml', $str);
        $output->writeln("tests/functional.suite.yml written - functional tests suite configuration");



        $suiteConfig = array(
            'class_name' => 'WebGuy',
            'modules' => array(
                'enabled' => array('PhpBrowser','WebHelper'),
                'config' => array(
                    'PhpBrowser' => array(
                        'url' => 'http://localhost/myapp/'
                    ),
                )
            ),
        );

        $str = $firstline;
        $str .= "# suite for acceptance tests.\n";
        $str .= "# perform tests in browser using the Selenium-like tools.\n";
        $str .= "# powered by Mink (http://mink.behat.org).\n";
        $str .= "# (tip: that's what your customer will see).\n";
        $str .= "# (tip: test your ajax and javascript by one of Mink drivers).\n\n";

        $str .= Yaml::dump($suiteConfig, 5);
        file_put_contents('tests/acceptance.suite.yml', $str);
        $output->writeln("tests/acceptance.suite.yml written - acceptance tests suite configuration");

        $output->writeln("<info>\nBootstrap is done. Check out /tests directory</info>");
        $output->writeln("<comment>To complete initialization run 'build' command</comment>");



    }


}