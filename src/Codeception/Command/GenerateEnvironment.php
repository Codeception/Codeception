<?php
namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Exception\ConfigurationException;
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
class GenerateEnvironment extends Command
{
    use Shared\FileSystem;
    use Shared\Config;

    protected function configure()
    {
        $this->setDefinition([
            new InputArgument('env', InputArgument::REQUIRED, 'Environment name'),
        ]);
    }

    public function getDescription()
    {
        return 'Generates empty environment config';
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $conf = $this->getGlobalConfig();
        if (!Configuration::envsDir()) {
            throw new ConfigurationException(
                "Path for environments configuration is not set.\n"
                . "Please specify envs path in your `codeception.yml`\n \n"
                . "envs: tests/_envs"
            );
        }
        $relativePath = $conf['paths']['envs'];
        $env = $input->getArgument('env');
        $file = "$env.yml";

        $path = $this->createDirectoryFor($relativePath, $file);
        $saved = $this->createFile($path . $file, "# `$env` environment config goes here");

        if ($saved) {
            $output->writeln("<info>$env config was created in $relativePath/$file</info>");
            return 0;
        } else {
            $output->writeln("<error>File $relativePath/$file already exists</error>");
            return 1;
        }
    }
}
