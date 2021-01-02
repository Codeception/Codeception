<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Codecept;
use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\ConfigurationException;
use Codeception\Lib\Console\Output;
use Codeception\Scenario;
use Codeception\SuiteManager;
use Codeception\Test\Cept;
use Codeception\Util\Debug;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

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
    protected $actions = [];

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('suite', InputArgument::REQUIRED, 'suite to be executed'),
            new InputOption('colors', '', InputOption::VALUE_NONE, 'Use colors in output'),
        ]);

        parent::configure();
    }

    public function getDescription()
    {
        return 'Launches interactive test console';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $suiteName = $input->getArgument('suite');
        $this->output = $output;

        $config = Configuration::config();
        $settings = Configuration::suiteSettings($suiteName, $config);

        $options = $input->getOptions();
        $options['debug'] = true;
        $options['silent'] = true;
        $options['interactive'] = false;
        $options['colors'] = true;

        Debug::setOutput(new Output($options));

        $this->codecept = new Codecept($options);
        $dispatcher = $this->codecept->getDispatcher();

        $suiteManager = new SuiteManager($dispatcher, $suiteName, $settings);
        $suiteManager->initialize();
        $this->suite = $suiteManager->getSuite();
        $moduleContainer = $suiteManager->getModuleContainer();

        $this->actions = array_keys($moduleContainer->getActions());

        $this->test = new Cept(null, null);
        $this->test->getMetadata()->setServices([
           'dispatcher' => $dispatcher,
           'modules' =>  $moduleContainer
        ]);

        $scenario = new Scenario($this->test);
        if (!$settings['actor']) {
            throw new ConfigurationException("Interactive shell can't be started without an actor");
        }
        if (isset($config["namespace"])) {
            $settings['actor'] = $config["namespace"] .'\\' . $settings['actor'];
        }
        $actor = $settings['actor'];
        $I = new $actor($scenario);

        $this->listenToSignals();

        $output->writeln("<info>Interactive console started for suite $suiteName</info>");
        $output->writeln("<info>Try Codeception commands without writing a test</info>");

        $suiteEvent = new SuiteEvent($this->suite, $this->codecept->getResult(), $settings);
        $dispatcher->dispatch($suiteEvent, Events::SUITE_INIT);
        $dispatcher->dispatch(new TestEvent($this->test), Events::TEST_PARSED);
        $dispatcher->dispatch(new TestEvent($this->test), Events::TEST_BEFORE);

        if (file_exists($settings['bootstrap'])) {
            require $settings['bootstrap'];
        }

        $I->pause();

        $dispatcher->dispatch(new TestEvent($this->test), Events::TEST_AFTER);
        $dispatcher->dispatch(new SuiteEvent($this->suite), Events::SUITE_AFTER);
        $output->writeln("<info>Bye-bye!</info>");
        return 0;
    }

    protected function listenToSignals()
    {
        if (function_exists('pcntl_signal')) {
            declare (ticks = 1);
            pcntl_signal(SIGINT, SIG_IGN);
            pcntl_signal(SIGTERM, SIG_IGN);
        }
    }
}
