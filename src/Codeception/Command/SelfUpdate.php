<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Codecept;
use Exception;
use Humbug\SelfUpdate\Updater;
use Phar;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

/**
 * Auto-updates phar archive from official site: 'https://codeception.com/codecept.phar' .
 *
 * * `php codecept.phar self-update`
 *
 * @author Franck Cassedanne <franck@cassedanne.com>
 */
class SelfUpdate extends Command
{
    /**
     * @var string
     */
    public const NAME = 'Codeception';

    /**
     * @var string
     */
    public const GITHUB_REPO = 'Codeception/Codeception';

    /**
     * @var string
     */
    public const PHAR_URL = 'https://codeception.com/php80/';

    /**
     * Holds the current script filename.
     */
    protected string $filename;

    protected function configure(): void
    {
        $this->filename = $_SERVER['argv'][0] ?? Phar::running(false);
        $this
            ->setAliases(['selfupdate'])
            ->setDescription(sprintf('Upgrade <comment>%s</comment> to the latest version', $this->filename));
    }

    protected function getCurrentVersion(): string
    {
        return Codecept::VERSION;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $output->writeln(
            sprintf('<info>%s</info> version <comment>%s</comment>', self::NAME, $this->getCurrentVersion())
        );

        $updater = new Updater(null, false);
        $updater->getStrategy()->setPharUrl(self::PHAR_URL . 'codecept.phar');
        $updater->getStrategy()->setVersionUrl(self::PHAR_URL . 'codecept.version');

        try {
            if ($updater->hasUpdate()) {
                $output->writeln("\n<info>Updating...</info>");
                $updater->update();

                $output->writeln("\n<comment>{$this->filename}</comment> has been updated.\n");
            } else {
                $output->writeln('You are already using the latest version.');
            }
        } catch (Exception $exception) {
            $output->writeln("<error>\n{$exception->getMessage()}\n</error>");
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
