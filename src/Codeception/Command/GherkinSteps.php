<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Test\Loader\Gherkin as GherkinLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use function count;

/**
 * Prints all steps from all Gherkin contexts for a specific suite
 *
 * ```
 * codecept gherkin:steps acceptance
 * ```
 *
 */
class GherkinSteps extends Command
{
    use Shared\ConfigTrait;
    use Shared\StyleTrait;

    protected function configure(): void
    {
        $this->setDefinition(
            [
                new InputArgument('suite', InputArgument::REQUIRED, 'suite to scan for feature files'),
                new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
            ]
        );
        parent::configure();
    }

    public function getDescription(): string
    {
        return 'Prints all defined feature steps';
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->addStyles($output);
        $suite = $input->getArgument('suite');
        $config = $this->getSuiteConfig($suite);
        $config['describe_steps'] = true;

        $loader = new GherkinLoader($config);
        $steps = $loader->getSteps();

        foreach ($steps as $name => $context) {
            $table = new Table($output);
            $table->setHeaders(['Step', 'Implementation']);
            $output->writeln("Steps from <bold>{$name}</bold> context:");

            foreach ($context as $step => $callable) {
                if (count($callable) < 2) {
                    continue;
                }
                $method = $callable[0] . '::' . $callable[1];
                $table->addRow([$step, $method]);
            }
            $table->render();
        }

        if (!isset($table)) {
            $output->writeln("No steps are defined, start creating them by running <bold>gherkin:snippets</bold>");
        }
        return Command::SUCCESS;
    }
}
