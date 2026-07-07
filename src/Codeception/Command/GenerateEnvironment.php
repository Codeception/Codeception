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

use function file_exists;

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
                . "Please specify envs path in your configuration file (`codeception.yml` or `codeception.php`)\n \n"
                . "envs: tests/_envs"
            );
        }

        $relativePath = $config['paths']['envs'];
        $env = $input->getArgument('env');

        if (Configuration::isPhpFormat()) {
            $file = $env . '.php';
            $contents = <<<PHP
<?php

declare(strict_types=1);

use Codeception\\Config\\SuiteConfig;

return SuiteConfig::create();

PHP;
        } else {
            $file = $env . '.yml';
            $contents = sprintf('# `%s` environment config goes here', $env);
        }

        $path = $this->createDirectoryFor($relativePath, $file);

        $otherFile = $env . (Configuration::isPhpFormat() ? '.yml' : '.php');
        if (file_exists($path . $otherFile)) {
            $output->writeln(sprintf(
                '<error>Environment "%s" already has %s/%s; a %s would silently shadow one of them '
                . '(PHP config wins over YAML). Remove %s or edit it directly.</error>',
                $env,
                $relativePath,
                $otherFile,
                $file,
                $otherFile
            ));
            return Command::FAILURE;
        }

        $saved = $this->createFile($path . $file, $contents);

        if ($saved) {
            $output->writeln(sprintf('<info>%s config was created in %s/%s</info>', $env, $relativePath, $file));
            return Command::SUCCESS;
        }

        $output->writeln(sprintf('<error>File %s/%s already exists</error>', $relativePath, $file));
        return Command::FAILURE;
    }
}
