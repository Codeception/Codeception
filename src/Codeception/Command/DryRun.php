<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Event\SuiteEvent;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Subscriber\Bootstrap as BootstrapLoader;
use Codeception\Subscriber\Console as ConsolePrinter;
use Codeception\SuiteManager;
use Codeception\Test\Interfaces\ScenarioDriven;
use Codeception\Test\Test;
use Codeception\Util\Maybe;
use Exception;
use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\EventDispatcher\EventDispatcher;
use function ini_set;
use function preg_match;
use function str_replace;
use function strpos;

/**
 * Shows step by step execution process for scenario driven tests without actually running them.
 *
 * * `codecept dry-run acceptance`
 * * `codecept dry-run acceptance MyCest`
 * * `codecept dry-run acceptance checkout.feature`
 * * `codecept dry-run tests/acceptance/MyCest.php`
 *
 */
class DryRun extends Command
{
    use Shared\ConfigTrait;
    use Shared\StyleTrait;

    protected function configure(): void
    {
        $this->setDefinition(
            [
                new InputArgument('suite', InputArgument::REQUIRED, 'suite to scan for feature files'),
                new InputArgument('test', InputArgument::OPTIONAL, 'tests to be loaded'),
            ]
        );
        parent::configure();
    }

    public function getDescription(): string
    {
        return 'Prints step-by-step scenario-driven test or a feature';
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->addStyles($output);
        $suite = $input->getArgument('suite');
        $test = $input->getArgument('test');

        $config = $this->getGlobalConfig();
        ini_set(
            'memory_limit',
            $config['settings']['memory_limit'] ?? '1024M'
        );
        if (! Configuration::isEmpty() && ! $test && strpos($suite, (string) $config['paths']['tests']) === 0) {
            [, $suite, $test] = $this->matchTestFromFilename($suite, $config['paths']['tests']);
        }
        $settings = $this->getSuiteConfig($suite);

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addSubscriber(new ConsolePrinter([
            'colors'    => (!$input->hasParameterOption('--no-ansi') xor $input->hasParameterOption('ansi')),
            'steps'     => true,
            'verbosity' => OutputInterface::VERBOSITY_VERBOSE,
        ]));
        $eventDispatcher->addSubscriber(new BootstrapLoader());

        $suiteManager = new SuiteManager($eventDispatcher, $suite, $settings);
        $moduleContainer = $suiteManager->getModuleContainer();
        foreach (Configuration::modules($settings) as $module) {
            $moduleContainer->mock($module, new Maybe());
        }
        $suiteManager->loadTests($test);
        $tests = $suiteManager->getSuite()->tests();

        $eventDispatcher->dispatch(new SuiteEvent($suiteManager->getSuite(), null, $settings), Events::SUITE_INIT);
        $eventDispatcher->dispatch(new SuiteEvent($suiteManager->getSuite(), null, $settings), Events::SUITE_BEFORE);

        foreach ($tests as $test) {
            if ($test instanceof Test && $test instanceof ScenarioDriven) {
                $this->dryRunTest($output, $eventDispatcher, $test);
            }
        }
        $eventDispatcher->dispatch(new SuiteEvent($suiteManager->getSuite()), Events::SUITE_AFTER);
        return Command::SUCCESS;
    }

    protected function matchTestFromFilename($filename, $testsPath)
    {
        $filename = str_replace(['//', '\/', '\\'], '/', $filename);
        $res = preg_match("#^{$testsPath}/(.*?)/(.*)$#", $filename, $matches);
        if (!$res) {
            throw new InvalidArgumentException("Test file can't be matched");
        }

        return $matches;
    }

    protected function dryRunTest(OutputInterface $output, EventDispatcher $eventDispatcher, Test $test): void
    {
        $eventDispatcher->dispatch(new TestEvent($test), Events::TEST_START);
        $eventDispatcher->dispatch(new TestEvent($test), Events::TEST_BEFORE);
        try {
            $test->test();
        } catch (Exception $e) {
        }
        $eventDispatcher->dispatch(new TestEvent($test), Events::TEST_AFTER);
        $eventDispatcher->dispatch(new TestEvent($test), Events::TEST_END);

        if ($test->getMetadata()->isBlocked()) {
            $output->writeln('');
            if ($skip = $test->getMetadata()->getSkip()) {
                $output->writeln("<warning> SKIPPED </warning>" . $skip);
            }
            if ($incomplete = $test->getMetadata()->getIncomplete()) {
                $output->writeln("<warning> INCOMPLETE </warning>" . $incomplete);
            }
        }
        $output->writeln('');
    }
}
