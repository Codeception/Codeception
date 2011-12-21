<?php
namespace Codeception\Command;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


class GenerateScenarios extends Base {

    public function getDescription() {
        return 'Generates text representation for all scenarios';
    }

	protected function execute(InputInterface $input, OutputInterface $output)
	{
        $this->initCodeception();
        @mkdir($path = $this->config['paths']['output'].'/scenarios');
        foreach ($this->suites as $suite => $settings) {
            $class = $settings['suite_class'];
            if (!class_exists($class)) continue;

            $output->writeln('Suite '.$suite.' started...');

            \Codeception\SuiteManager::init($settings);

            $testManager = new \Codeception\SuiteManager(new $class, false);
            if (isset($settings['bootstrap'])) $testManager->setBootstrtap($settings['bootstrap']);
            $testManager->loadCepts($this->tests_path.'/'.$suite);
            $tests = $testManager->getCurrentSuite()->tests();

            @mkdir($path.'/'.$suite);

            foreach ($tests as $test) {
               $test->loadScenario();
               $features = $test->getScenarioText();
               $name = $this->underscore($test->getName());

               $output->writeln("* $name generated");
               file_put_contents($path.'/'.$suite.'/'.$name.'.txt', $features);

            }

        }
	}

    private function underscore($name)
    {
        $name = preg_replace('/([A-Z]+)([A-Z][a-z])/','\\1_\\2', $name);
        $name = preg_replace('/([a-z\d])([A-Z])/','\\1_\\2', $name);
        return $name;

    }

}
