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
                'tests' => 'cepts',
                'output' => 'cepts/_log',
                'helpers' => 'cepts/helpers',
                'modules' => 'cepts/modules'
            ),
            'settings' => array(
                'bootstrap' => '_bootstrap.php',
                'suite_class' => '\PHPUnit_Framework_TestSuite',
                'colors' => true,
                'memory_limit' => '1024M'
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

        @mkdir('cepts');
        @mkdir('cepts/functional');
        @mkdir('cepts/unit');
        @mkdir('cepts/acceptance');
        @mkdir('cepts/helpers');
        @mkdir('cepts/_log');
        @mkdir('cepts/_data');

        $output->writeln("cepts/unit created - unit tests");
        $output->writeln("cepts/functional created - functional tests");
        $output->writeln("cepts/acceptance created - acceptance tests");

        file_put_contents('cepts/_data/dump.sql', '/* Replace this file with actual dump of your database */');

        file_put_contents('cepts/unit/_bootstrap.php', "<?php\n// Here you can initialize variables that will for your tests\n");
        file_put_contents('cepts/functional/_bootstrap.php', "<?php\n// Here you can initialize variables that will for your tests\n");
        file_put_contents('cepts/acceptance/_bootstrap.php', "<?php\n// Here you can initialize variables that will for your tests\n");


        file_put_contents('cepts/helpers/CodeHelper.php', "<?php\nnamespace Codeception\\Module;\n\n// here you can define custom functions for CodeGuy \n\nclass CodeHelper extends \\Codeception\\Module\n{\n}\n");
        file_put_contents('cepts/helpers/TestHelper.php', "<?php\nnamespace Codeception\\Module;\n\n// here you can define custom functions for TestGuy \n\nclass TestHelper extends \\Codeception\\Module\n{\n}\n");
        file_put_contents('cepts/helpers/WebHelper.php', "<?php\nnamespace Codeception\\Module;\n\n// here you can define custom functions for WebGuy \n\nclass WebHelper extends \\Codeception\\Module\n{\n}\n");

        file_put_contents('cepts/unit/SampleSpec.php', "<?php\n\$I = new CodeGuy(\$scenario);\n\$I->wantTo('test a code specification');");
        file_put_contents('cepts/functional/SampleSpec.php', "<?php\n\$I = new TestGuy(\$scenario);\n\$I->wantTo('test an integration feature');");
        file_put_contents('cepts/acceptance/SampleSpec.php', "<?php\n\$I = new WebGuy(\$scenario);\n\$I->wantTo('test an application feature in a web browser');");

        // CodeGuy
        $conf = array(
            'class_name' => 'CodeGuy',
            'modules' => array('enabled' => array('Unit','CodeHelper')),

        );

        $firstline = $str  = "# Codeception Test Suite Configuration\n\n";
        $str .= "# suite for unit (internal) tests.\n";
        $str .= Yaml::dump($conf, 2);

        file_put_contents('cepts/unit.suite.yml', $str);
        $output->writeln("cepts/unit.suite.yml written - unit tests suite configuration");


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

        file_put_contents('cepts/functional.suite.yml', $str);
        $output->writeln("cepts/functional.suite.yml written - functional tests suite configuration");



        $suiteConfig = array(
            'class_name' => 'WebGuy',
            'modules' => array(
                'enabled' => array('PhpBrowser','WebHelper'),
                'config' => array(
                    'PhpBrowser' => array(
                        'start' => 'http://localhost/myapp/',
                        'output' => 'cepts/_log'
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
        file_put_contents('cepts/acceptance.suite.yml', $str);
        $output->writeln("cepts/acceptance.suite.yml written - acceptance tests suite configuration");

        $output->writeln("<info>\nBootstrap is done. Check out /tests directory</info>");
        $output->writeln("<comment>To complete initialization run 'build' command</comment>");



    }


}