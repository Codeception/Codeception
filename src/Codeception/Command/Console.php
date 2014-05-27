<?php

namespace Codeception\Command;

use Codeception\Actor;
use Codeception\Codecept;
use Codeception\Events;
use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Scenario;
use Codeception\SuiteManager;
use Codeception\TestCase\Cept;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Try to execute test commands in run-time. You may try commands before writing the test.
 *
 * * `codecept console acceptance` - starts acceptance suite environment. If you use WebDriver you can manipulate browser with Codeception commands.
 */
class Console extends Command
{
    protected $test;
    protected $codecept;
    protected $suite;
    protected $output;

    protected function configure()
    {
        $this->setDefinition(
             array(
                 new InputArgument('suite', InputArgument::REQUIRED, 'suite to be executed'),
                 new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
                 new InputOption('colors', '', InputOption::VALUE_NONE, 'Use colors in output'),
             )
        );

        parent::configure();
    }

    public function getDescription()
    {
        return 'Launches interactive test console';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suiteName    = $input->getArgument('suite');
        $this->output = $output;

        $config   = Configuration::config($input->getOption('config'));
        $settings = Configuration::suiteSettings($suiteName, $config);

        $options          = $input->getOptions();
        $options['debug'] = true;
        $options['steps'] = true;

        $this->codecept = new Codecept($options);
        $dispatcher     = $this->codecept->getDispatcher();
        $suiteManager   = new SuiteManager($dispatcher, $suiteName, $settings);
        $this->suite    = $suiteManager->getSuite();
        $this->test     = new Cept($dispatcher, array('name' => 'interactive', 'file' => 'interactive'));

        $scenario = new Scenario($this->test);
        $guy      = $settings['class_name'];
        $I        = new $guy($scenario);

        $this->listenToSignals();

        $output->writeln("<info>Interactive console started for suite $suiteName</info>");
        $output->writeln("<info>Try Codeception commands without writing a test</info>");
        $output->writeln("<info>type 'exit' to leave console</info>");
        $output->writeln("<info>type 'actions' to see all available actions for this suite</info>");

        $suiteEvent = new SuiteEvent($this->suite, $this->codecept->getResult(), $settings);
        $dispatcher->dispatch(Events::SUITE_BEFORE, $suiteEvent);

        $dispatcher->dispatch(Events::TEST_PARSED, new TestEvent($this->test));
        $dispatcher->dispatch(Events::TEST_BEFORE, new TestEvent($this->test));

        $output->writeln("\n\n\$I = new {$settings['class_name']}(\$scenario);");
        $scenario->run();
        $this->executeCommands($output, $I, $settings['bootstrap']);

        $dispatcher->dispatch(Events::TEST_AFTER, new TestEvent($this->test));
        $dispatcher->dispatch(Events::SUITE_AFTER, new SuiteEvent($this->suite));

        $output->writeln("<info>Bye-bye!</info>");
    }

    protected function executeCommands(OutputInterface $output, Actor $I, $bootstrap)
    {
        $dialog = $this->getHelperSet()->get('dialog');

        if (file_exists($bootstrap)) {
            require $bootstrap;
        }

        do {
            $command = $dialog->ask($output, '$I->', null, array_keys(SuiteManager::$actions));
            if ($command == 'actions') {
                $output->writeln("<info>" . implode(' ', array_keys(SuiteManager::$actions)));
                continue;
            };
            if ($command == 'exit') {
                return;
            }
            if ($command == '') {
                continue;
            }
            try {
                eval("\$I->$command;");
            } catch (\PHPUnit_Framework_AssertionFailedError $fail) {
                $output->writeln("<error>fail</error> " . $fail->getMessage());
            } catch (\Exception $e) {
                $output->writeln("<error>error</error> " . $e->getMessage());
            }
        } while (true);
    }

    protected function listenToSignals()
    {
        if (function_exists('pcntl_signal')) {
            declare(ticks = 1);
            pcntl_signal(SIGINT, SIG_IGN);
            pcntl_signal(SIGTERM, SIG_IGN);
        }
    }
}
