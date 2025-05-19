<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates empty environment configuration file into envs dir:
 *
 *  * `codecept g:env firefox`
 *
 * Required to have `envs` path to be specified in `codeception.yml`
 */
#[AsCommand(
    name: 'generate:environment',
    description: 'Generates empty environment config'
)]
class GenerateEnvironment extends Command
{
    use Shared\FileSystemTrait;
    use Shared\ConfigTrait;

    protected function configure(): void
    {
        $this->addArgument('env', InputArgument::REQUIRED, 'Environment name');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $config = $this->getGlobalConfig();
        if (Configuration::envsDir() === '') {
            throw new ConfigurationException(
                "Path for environments configuration is not set.\n"
                . "Please specify envs path in your `codeception.yml`\n \n"
                . "envs: tests/_envs"
            );
        }

        $relativePath = $config['paths']['envs'];
        $env = $input->getArgument('env');
        $file = $env . '.yml';

        $path = $this->createDirectoryFor($relativePath, $file);
        $saved = $this->createFile($path . $file, sprintf('# `%s` environment config goes here', $env));

        if ($saved) {
            $output->writeln(sprintf('<info>%s config was created in %s/%s</info>', $env, $relativePath, $file));
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('<error>File %s/%s already exists</error>', $relativePath, $file));
        return Command::FAILURE;
    }
}
