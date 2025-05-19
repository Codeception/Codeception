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
use Codeception\Suite;
use Codeception\SuiteManager;
use Codeception\Test\Cept;
use Codeception\Util\Debug;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function array_keys;
use function file_exists;
use function function_exists;
use function pcntl_signal;

/**
 * Try to execute test commands in run-time. You may try commands before writing the test.
 *
 * * `codecept console acceptance` - starts acceptance suite environment. If you use WebDriver you can manipulate browser with Codeception commands.
 */
#[AsCommand(
    name: 'console',
    description: 'Launches interactive test console'
)]
class Console extends Command
{
    protected ?Cept $test = null;

    protected ?Codecept $codecept = null;

    protected ?Suite $suite = null;

    protected ?OutputInterface $output = null;

    /**
     * @var string[]
     */
    protected array $actions = [];

    protected function configure(): void
    {
        $this
            ->addArgument('suite', InputArgument::REQUIRED, 'suite to be executed')
            ->addOption('colors', null, InputOption::VALUE_NONE, 'Use colors in output');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
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
        $eventDispatcher = $this->codecept->getDispatcher();

        $suiteManager = new SuiteManager($eventDispatcher, $suiteName, $settings, []);
        $suiteManager->initialize();

        $this->suite = $suiteManager->getSuite();
        $moduleContainer = $suiteManager->getModuleContainer();

        $this->actions = array_keys($moduleContainer->getActions());

        $this->test = new Cept('', '');
        $this->test->getMetadata()->setServices([
            'dispatcher' => $eventDispatcher,
            'modules'    => $moduleContainer,
        ]);

        $scenario = new Scenario($this->test);
        if (!$settings['actor']) {
            throw new ConfigurationException("Interactive shell can't be started without an actor");
        }

        if (isset($config['namespace']) && $config['namespace'] !== '') {
            $settings['actor'] = $config['namespace'] . '\\Support\\' . $settings['actor'];
        }

        $actor = $settings['actor'];
        $I = new $actor($scenario);

        $this->listenToSignals();

        $output->writeln("<info>Interactive console started for suite {$suiteName}</info>");
        $output->writeln("<info>Try Codeception commands without writing a test</info>");

        $suiteEvent = new SuiteEvent($this->suite, $settings);
        $eventDispatcher->dispatch($suiteEvent, Events::SUITE_INIT);
        $eventDispatcher->dispatch(new TestEvent($this->test), Events::TEST_PARSED);
        $eventDispatcher->dispatch(new TestEvent($this->test), Events::TEST_BEFORE);

        if (is_string($settings['bootstrap']) && file_exists($settings['bootstrap'])) {
            require $settings['bootstrap'];
        }

        $I->pause();

        $eventDispatcher->dispatch(new TestEvent($this->test), Events::TEST_AFTER);
        $eventDispatcher->dispatch(new SuiteEvent($this->suite), Events::SUITE_AFTER);

        $output->writeln("<info>Bye-bye!</info>");
        return Command::SUCCESS;
    }

    protected function listenToSignals(): void
    {
        if (function_exists('pcntl_signal')) {
            declare(ticks=1);
            pcntl_signal(SIGINT, SIG_IGN);
            pcntl_signal(SIGTERM, SIG_IGN);
        }
    }
}
