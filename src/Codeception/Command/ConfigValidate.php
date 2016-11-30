<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Validates and prints Codeception config.
 * Use it do debug Yaml configs
 *
 * Check config:
 *
 * * `codecept config`: check global config
 * * `codecept config unit`: check suite config
 *
 * Load config:
 *
 * * `codecept config:validate -c path/to/another/config`: from another dir
 * * `codecept config:validate -c another_config.yml`: from another config file
 *
 * Check overriding config values (like in `run` command)
 *
 * * `codecept config:validate -o "settings: shuffle: true"`: enable shuffle
 * * `codecept config:validate -o "settings: lint: false"`: disable linting
 * * `codecept config:validate -o "reporters: report: \Custom\Reporter" --report`: use custom reporter
 *
 */
class ConfigValidate extends Command
{
    use Shared\Config;
    use Shared\Style;

    protected function configure()
    {
        $this->setDefinition(
            [
                new InputArgument('suite', InputArgument::OPTIONAL, 'to show suite configuration'),
                new InputOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Use custom path for config'),
                new InputOption('override', 'o', InputOption::VALUE_IS_ARRAY | InputOption::VALUE_REQUIRED, 'Override config values'),
            ]
        );
        parent::configure();
    }

    public function getDescription()
    {
        return 'Validates and prints config to screen';
    }


    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->addStyles($output);

        if ($input->getArgument('suite')) {
            $config = $this->getSuiteConfig($input->getArgument('suite'), $input->getOption('config'));
            $output->writeln($this->formatOutput($config));
            return;
        }

        $output->write("Validating global config... ");
        $config = Configuration::config($input->getOption('config'));
        $output->writeln($input->getOption('override'));
        if (count($input->getOption('override'))) {
            $config = $this->overrideConfig($input->getOption('override'));
        }
        $suites = Configuration::suites();
        $output->writeln("Ok");

        $output->writeln("------------------------------\n");
        $output->writeln("<info>Codeception Config</info>:\n");
        $output->writeln($this->formatOutput($config));

        $output->writeln("Available suites: " . implode(', ', $suites));
        $output->writeln('');

        foreach ($suites as $suite) {
            $output->write("Validating suite <bold>$suite</bold>... ");
            $this->getSuiteConfig($suite, $input->getOption('config'));
            $output->writeln('Ok');
        }
        $output->writeln("Execute <info>codecept config:validate [<suite>]</info> to see config for a suite");
    }

    protected function formatOutput($config)
    {
        $output = print_r($config, true);
        return preg_replace('~\[(.*?)\] =>~', "<fg=yellow>$1</fg=yellow> =>", $output);
    }
}
