<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Lib\Generator\Helper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function ucfirst;

/**
 * Creates empty Helper class.
 *
 * * `codecept g:helper MyHelper`
 * * `codecept g:helper "My\Helper"`
 *
 */
class GenerateHelper extends Command
{
    use Shared\FileSystemTrait;
    use Shared\ConfigTrait;

    protected function configure(): void
    {
        $this->setDefinition([
            new InputArgument('name', InputArgument::REQUIRED, 'helper name'),
        ]);
    }

    public function getDescription(): string
    {
        return 'Generates new helper';
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = ucfirst((string)$input->getArgument('name'));
        $config = $this->getGlobalConfig();

        $path = $this->createDirectoryFor(Configuration::supportDir() . 'Helper', $name);
        $filename = $path . $this->getShortClassName($name) . '.php';

        $res = $this->createFile($filename, (new Helper($config, $name))->produce());
        if ($res) {
            $output->writeln("<info>Helper {$filename} created</info>");
            return 0;
        } else {
            $output->writeln("<error>Error creating helper {$filename}</error>");
            return 1;
        }
    }
}
