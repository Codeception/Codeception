<?php
namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateScenarios extends Base
{

    protected function configure()
    {
        $this->setDefinition(array(
            new \Symfony\Component\Console\Input\InputArgument('suite', InputArgument::REQUIRED, 'suite from which tests should be generated'),
        ));
        parent::configure();
    }

    public function getDescription()
    {
        return 'Generates text representation for all scenarios';
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');

        $config = \Codeception\Configuration::config();
        $suiteconf = \Codeception\Configuration::suiteSettings($suite, $config);

        @mkdir($path = \Codeception\Configuration::dataDir() . 'scenarios');
        @mkdir($path = $path . DIRECTORY_SEPARATOR . $suite);

        $dispatcher = new \Symfony\Component\EventDispatcher\EventDispatcher();

        $suiteManager = new \Codeception\SuiteManager($dispatcher, $suite, $suiteconf);

        if (isset($suiteconf['bootstrap'])) {
            if (file_exists($suiteconf['path'] . $suiteconf['bootstrap'])) {
                require_once $suiteconf['path'] . $suiteconf['bootstrap'];
            }
        }

        $suiteManager->loadTests();
        $tests = $suiteManager->getSuite()->tests();

        foreach ($tests as $test) {
            if (!($test instanceof \Codeception\TestCase\Cept)) continue;
            $test->loadScenario();
            $features = $test->getScenarioText();
            $name = $this->underscore(substr($test->getFileName(), 0, -8));

            $output->writeln("* $name generated");
            file_put_contents($path . DIRECTORY_SEPARATOR . $name . '.txt', $features);
        }
    }

    private function underscore($name)
    {
        $name = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1_\\2', $name);
        $name = preg_replace('/([a-z\d])([A-Z])/', '\\1_\\2', $name);
        $name = str_replace(array('/','\\'),array('.','.'), $name);
        return $name;

    }

}
