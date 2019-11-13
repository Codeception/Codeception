<?php
namespace Codeception\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
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
    const PHAR_URL = 'http://codeception.com/codecept.phar';
    const PHAR_URL_PHP56 = 'http://codeception.com/php56/codecept.phar';

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
            ->setDescription(
                sprintf(
                    'Upgrade <comment>%s</comment> to the latest version',
                    $this->filename
                )
            );

        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $currentVersion = Codecept::VERSION;

        $output->writeln(
            sprintf(
                '<info>%s</info> version <comment>%s</comment>',
                self::NAME,
                $currentVersion
            )
        );

        try {
            $output->writeln("\n<info>Updating...</info>");
            $this->retrievePharFile($output);
        } catch (\Exception $e) {
            $output->writeln(
                sprintf(
                    "<error>\n%s\n</error>",
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Retrieves the latest phar file.
     *
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function retrievePharFile(OutputInterface $output)
    {
        $temp = basename($this->filename, '.phar') . '-temp.phar';

        try {
            $sourceUrl = $this->getPharUrl();
            if (@copy($sourceUrl, $temp)) {
                chmod($temp, 0777 & ~umask());

                // test the phar validity
                $phar = new \Phar($temp);
                // free the variable to unlock the file
                unset($phar);
                rename($temp, $this->filename);
            } else {
                throw new \Exception('Request failed.');
            }
        } catch (\Exception $e) {
            if (!$e instanceof \UnexpectedValueException
                && !$e instanceof \PharException
            ) {
                throw $e;
            }
            unlink($temp);

            $output->writeln(
                sprintf(
                    "<error>\nSomething went wrong (%s).\nPlease re-run this again.</error>\n",
                    $e->getMessage()
                )
            );
        }

        $output->writeln(
            sprintf(
                "\n<comment>%s</comment> has been updated.\n",
                $this->filename
            )
        );
    }

    /**
     * Returns Phar file URL for current version of PHP
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
