<?php
namespace Codeception\Command;

use Codeception\Test\Loader\Gherkin;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

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
    use Shared\Config;
    use Shared\Style;

    protected function configure()
    {
        $this->setDefinition(
            [
                new InputArgument('suite', InputArgument::REQUIRED, 'suite to scan for feature files'),
                new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
            ]
        );
        parent::configure();
    }

    public function getDescription()
    {
        return 'Prints all defined feature steps';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addStyles($output);
        $suite = $input->getArgument('suite');
        $config = $this->getSuiteConfig($suite);
        $config['describe_steps'] = true;

        $loader = new Gherkin($config);
        $steps = $loader->getSteps();

        foreach ($steps as $name => $context) {
            /** @var $table Table  **/
            $table = new Table($output);
            $table->setHeaders(['Step', 'Implementation']);
            $output->writeln("Steps from <bold>$name</bold> context:");

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
    }
}
