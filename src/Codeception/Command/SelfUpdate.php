<?php
namespace Codeception\Command;

use Humbug\SelfUpdate\Updater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Codeception\Codecept;

/**
 * Auto-updates phar archive from official site: 'http://codeception.com/codecept.phar' .
 *
 * * `php codecept.phar self-update`
 *
 * @author Franck Cassedanne <franck@cassedanne.com>
 */
class SelfUpdate extends Command
{
    /**
     * Class constants
     */
    const NAME = 'Codeception';
    const GITHUB_REPO = 'Codeception/Codeception';
    const PHAR_URL = 'http://codeception.com/';
    const PHAR_URL_PHP56 = 'http://codeception.com/php56/';

    /**
     * Holds the current script filename.
     * @var string
     */
    protected $filename;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        if (isset($_SERVER['argv'][0])) {
            $this->filename = $_SERVER['argv'][0];
        } else {
            $this->filename = \Phar::running(false);
        }

        $this
            ->setAliases(array('selfupdate'))
            ->setDescription(
                sprintf(
                    'Upgrade <comment>%s</comment> to the latest version',
                    $this->filename
                )
            );

        parent::configure();
    }

    /**
     * @return string
     */
    protected function getCurrentVersion()
    {
        return Codecept::VERSION;
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $version = $this->getCurrentVersion();

        $output->writeln(
            sprintf(
                '<info>%s</info> version <comment>%s</comment>',
                self::NAME,
                $version
            )
        );

        $url = $this->getPharUrl();

        $updater = new Updater(null, false);
        $updater->getStrategy()->setPharUrl($url . 'codecept.phar');
        $updater->getStrategy()->setVersionUrl($url . 'codecept.version');

        try {
            if ($updater->hasUpdate()) {
                $output->writeln("\n<info>Updating...</info>");
                $updater->update();

                $output->writeln(
                    sprintf("\n<comment>%s</comment> has been updated.\n", $this->filename)
                );
            } else {
                $output->writeln('You are already using the latest version.');
            }
        } catch (\Exception $e) {
            $output->writeln(
                sprintf(
                    "<error>\n%s\n</error>",
                    $e->getMessage()
                )
            );
            return 1;
        }

        return 0;
    }

    /**
     * Returns base url of phar file for current PHP version
     *
     * @return string
     */
    protected function getPharUrl()
    {
        if (version_compare(PHP_VERSION, '7.2.0', '<')) {
            return self::PHAR_URL_PHP56;
        }
        return self::PHAR_URL;
    }
}
