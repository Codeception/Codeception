<?php
namespace Codeception\Command;

use Codeception\Lib\Generator\Helper;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Yaml;


class GenerateSuite extends Base
{
    protected function configure()
    {
        $this->setDefinition(
            array(
                new InputArgument('suite', InputArgument::REQUIRED, 'suite to be generated'),
                new InputArgument('actor', InputArgument::OPTIONAL, 'name of new actor class'),
                new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
            )
        );
    }

    public function getDescription()
    {
        return 'Generates new test suite';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = lcfirst($input->getArgument('suite'));
        $actor = $input->getArgument('actor');

        $config = \Codeception\Configuration::config($input->getOption('config'));
        if (!$actor) {
            $actor = ucfirst($suite) . $config['actor'];
        }
        $config['class_name'] = $actor;

        $dir = \Codeception\Configuration::testsDir();
        if (file_exists($dir . $suite)) {
            throw new \Exception("Directory $suite already exists.");
        }
        if (file_exists($dir . $suite . '.suite.yml')) {
            throw new \Exception("Suite configuration file '$suite.suite.yml' already exists.");
        }

        $this->buildPath($dir . $suite . DIRECTORY_SEPARATOR, '_bootstrap.php');

        // generate bootstrap
        $this->save($dir . $suite . DIRECTORY_SEPARATOR . '_bootstrap.php',
            "<?php\n// Here you can initialize variables that will for your tests\n",
            true
        );
        $actorName = $this->removeSuffix($actor, $config['actor']);

        // generate helper
        $this->save(
            \Codeception\Configuration::helpersDir() . $actorName . 'Helper.php',
            (new Helper($actorName, $config['namespace']))->produce()
        );

        $conf = array(
            'class_name' => $actorName.$config['actor'],
            'modules' => array(
                'enabled' => array($actorName . 'Helper')
            ),
        );

        $this->save($dir . $suite . '.suite.yml', Yaml::dump($conf, 2));

        $output->writeln("<info>Suite $suite generated</info>");
    }
}
