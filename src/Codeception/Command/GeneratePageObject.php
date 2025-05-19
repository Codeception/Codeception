<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Configuration;
use Codeception\Lib\Generator\PageObject as PageObjectGenerator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function ucfirst;

/**
 * Generates PageObject. Can be generated either globally, or just for one suite.
 * If PageObject is generated globally it will act as UIMap, without any logic in it.
 *
 * * `codecept g:page Login`
 * * `codecept g:page Registration`
 * * `codecept g:page acceptance Login`
 */
#[AsCommand(
    name: 'generate:pageobject',
    description: 'Generates empty PageObject class'
)]
class GeneratePageObject extends Command
{
    use Shared\FileSystemTrait;
    use Shared\ConfigTrait;

    protected function configure(): void
    {
        $this
            ->addArgument('suite', InputArgument::REQUIRED, 'Either suite name or page object name')
            ->addArgument('page', InputArgument::OPTIONAL, 'Page name of pageobject to represent');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $suite = (string)$input->getArgument('suite');
        $class = $input->getArgument('page');

        if (!$class) {
            $class = $suite;
            $suite = '';
        }

        $conf = $suite
            ? $this->getSuiteConfig($suite)
            : $this->getGlobalConfig();

        if ($suite) {
            $suite = DIRECTORY_SEPARATOR . ucfirst($suite);
        }

        $path = $this->createDirectoryFor(Configuration::supportDir() . 'Page' . $suite, $class);

        $filename = $path . $this->getShortClassName($class) . '.php';

        $output->writeln($filename);

        $pageObject = new PageObjectGenerator($conf, ucfirst($suite) . '\\' . $class);
        $res = $this->createFile($filename, $pageObject->produce());

        if (!$res) {
            $output->writeln("<error>PageObject {$filename} already exists</error>");
            return Command::FAILURE;
        }
        $output->writeln("<info>PageObject was created in {$filename}</info>");
        return Command::SUCCESS;
    }
}
