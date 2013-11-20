<?php

namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\SuiteManager;
use Codeception\TestCase\Cept;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Analyze extends Base
{
    protected $methodTemplate = <<<EOF

    /**
     * Stub method used in a test file.
     *
     * Expected Usage:
     *
     * ``` php
     * %s
     * ```
     */
    public function %s(%s) {
        \$this->debugSection("Empty","This action is empty");
    }

}
EOF;

    protected function configure()
    {
        $this
            ->setDefinition($this->createDefinition())
            ->setDescription('Analyzes for non-existent methods and adds them to corresponding helper');

        parent::configure();
    }

    /**
     * @return InputDefinition
     */
    protected function createDefinition()
    {
        return new InputDefinition(
            array(
                 new InputArgument('suite', InputArgument::REQUIRED, 'suite to analyze'),
                 new InputOption('config', 'c', InputOption::VALUE_REQUIRED, 'Use specified config instead of default'),
            )
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $suite = $input->getArgument('suite');

        $output->writeln('Warning: this command may affect your Helper classes');

        $config    = Configuration::config($input->getOption('config'));
        $suiteconf = Configuration::suiteSettings($suite, $config);

        $dispatcher   = new EventDispatcher();
        $suiteManager = new SuiteManager($dispatcher, $suite, $suiteconf);

        if (isset($suiteconf['bootstrap'])) {
            if (file_exists($suiteconf['path'] . $suiteconf['bootstrap'])) {
                require_once $suiteconf['path'] . $suiteconf['bootstrap'];
            }
        }

        $suiteManager->loadTests();
        $tests = $suiteManager->getSuite()->tests();

        $dialog = $this->getHelperSet()->get('dialog');

        $helper = $this->matchHelper();
        if (!$helper) {
            $output->writeln("<error>No helpers for suite $suite is defined. Can't append new methods</error>");
            return;
        }

        if (!file_exists($helper_file = Configuration::helpersDir() . $helper . '.php')) {
            $output->writeln("<error>Helper class $helper.php doesn't exist</error>");
            return;
        }

        $replaced = 0;
        $analyzed = 0;

        foreach ($tests as $test) {
            if (!($test instanceof Cept)) {
                continue;
            }
            $analyzed++;
            $test->testCodecept(false);
            $scenario = $test->getScenario();

            foreach ($scenario->getSteps() as $step) {
                if ($step->getName() == 'Comment') {
                    continue;
                }
                $action = $step->getAction();

                if (isset(SuiteManager::$actions[$action])) {
                    continue;
                }
                if (!$dialog->askConfirmation(
                    $output,
                    "<question>\nAction '$action' is missing. Do you want to add it to helper class?\n</question>\n",
                    false
                )
                ) {
                    continue;
                }

                $example = sprintf('$I->%s(%s);', $action, $step->getArguments(true));

                $args = array_map(
                    function ($a) {
                        return '$arg' . $a;
                    },
                    range(1, count($step->getArguments()))
                );

                $stub = sprintf($this->methodTemplate, $example, $action, implode(', ', $args));

                $contents = file_get_contents($helper_file);
                $contents = preg_replace('~}(?!.*})~ism', $stub, $contents);
                $this->save($helper_file, $contents, true);

                $output->writeln("Action '$action' added to helper $helper");
                $replaced++;
            }
        }

        $output->writeln("<info>Analysis finished. $analyzed tests analyzed. $replaced methods added</info>");
        $output->writeln("Run the 'build' command to finish");
    }

    private function matchHelper()
    {
        $modules = array_keys(SuiteManager::$modules);
        foreach ($modules as $module) {
            if (preg_match('~Helper$~', $module)) {
                return $module;
            }
        }
    }
}
