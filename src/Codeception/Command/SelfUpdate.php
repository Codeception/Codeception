<?php

declare(strict_types=1);

namespace Codeception\Command;

use Codeception\Codecept;
use Codeception\Lib\SelfUpdate\GithubReleaseStrategy;
use Exception;
use Humbug\SelfUpdate\Updater;
use Phar;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

use function sprintf;

/**
 * Updates the phar archive to the latest release of the same major version from GitHub:
 * https://github.com/Codeception/Codeception/releases
 *
 * * `php codecept.phar self-update`
 *
 * @author Franck Cassedanne <franck@cassedanne.com>
 */
#[AsCommand(
    name: 'self-update',
    aliases: ['selfupdate']
)]
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
     * Holds the current script filename.
     */
    protected string $filename;

    protected function configure(): void
    {
        $this->filename = $_SERVER['argv'][0] ?? Phar::running(false);
        $this->setDescription(sprintf('Upgrade <comment>%s</comment> to the latest version', $this->filename));
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
        $updater->setStrategyObject(new GithubReleaseStrategy($this->getCurrentVersion()));

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
