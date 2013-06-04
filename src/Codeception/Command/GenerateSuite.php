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

        $dir = \Codeception\Configuration::projectDir().$config['paths']['tests'];

        $suitePath = $dir . DIRECTORY_SEPARATOR . $suite;
        $paths = explode(DIRECTORY_SEPARATOR, $suitePath);
        $prevPath = '';

        foreach ($paths as $path) {
            $prevPath = $prevPath . $path . DIRECTORY_SEPARATOR;

            if(!is_dir($prevPath)) {
                mkdir($prevPath);
            }
        }

        if (file_exists($dir.$suite.'.suite.yml')) throw new \Exception("Suite configuration file '$suite.suite.yml' already exists.");
        @mkdir($dir.DIRECTORY_SEPARATOR.$suite);

        // generate bootstrap
        file_put_contents($dir.DIRECTORY_SEPARATOR.$suite.'/_bootstrap.php', "<?php\n// Here you can initialize variables that will for your tests\n");


        if (strpos(strrev($guy),'yuG') !== 0) $guy = $guy.'Guy';
        $guyname = substr($guy,0,-3);

        // generate helper
        file_put_contents(\Codeception\Configuration::projectDir().$config['paths']['helpers'].DIRECTORY_SEPARATOR.$guyname.'Helper.php', "<?php\nnamespace Codeception\\Module;\n\n// here you can define custom functions for $guy \n\nclass {$guyname}Helper extends \\Codeception\\Module\n{\n}\n");

        $conf = array(
            'class_name' => $guy,
            'modules' => array('enabled' => array($guyname.'Helper')),
        );

        file_put_contents($dir.DIRECTORY_SEPARATOR.$suite.'.suite.yml', Yaml::dump($conf, 2));

        $output->writeln("<info>Suite $suite generated</info>");
    }
}
