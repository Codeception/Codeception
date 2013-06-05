<?php
namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;


class GenerateSuite extends Base
{
    protected function configure()
    {
        $this->setDefinition(array(
            new InputArgument('suite', InputArgument::REQUIRED, 'suite to be generated'),
            new InputArgument('guy', InputArgument::REQUIRED, 'name of new Guy class'),
            new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
        ));
        parent::configure();
    }

    public function getDescription() 
    {
        return 'Generates new test suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');
        $guy = $input->getArgument('guy');

        $config = \Codeception\Configuration::config($input->getOption('config'));
        $namespace = $config['namespace'] ? $config['namespace'] . '\\' : '';

        $dir = \Codeception\Configuration::projectDir().$config['paths']['tests'].DIRECTORY_SEPARATOR;
        if (file_exists($dir.$suite)) throw new \Exception("Directory $suite already exists.");
        if (file_exists($dir.$suite.'.suite.yml')) throw new \Exception("Suite configuration file '$suite.suite.yml' already exists.");

        $this->buildPath($dir.$suite.DIRECTORY_SEPARATOR, '_bootstrap.php');

        // generate bootstrap
        $this->save($dir.$suite.DIRECTORY_SEPARATOR.'_bootstrap.php', "<?php\n// Here you can initialize variables that will for your tests\n", true);

        $guyname = $this->removeSuffix($guy, 'Guy');

        // generate helper
        $this->save(\Codeception\Configuration::projectDir().$config['paths']['helpers'].DIRECTORY_SEPARATOR.$guyname.'Helper.php',
            "<?php\nnamespace {$namespace}Codeception\\Module;\n\n// here you can define custom functions for $guy \n\nclass {$guyname}Helper extends \\Codeception\\Module\n{\n}\n");

        $conf = array(
            'class_name' => $guy,
            'modules' => array('enabled' => array($guyname.'Helper'))   ,
        );

        $this->save($dir.$suite.'.suite.yml', Yaml::dump($conf, 2));

        $output->writeln("<info>Suite $suite generated</info>");
    }
}
