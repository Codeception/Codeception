<?php
namespace Codeception\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
    const GITHUB = 'Codeception/Codeception';
    const SOURCE = 'http://codeception.com/codecept.phar';

    /**
     * Holds the current script filename.
     * @var string
     */
    protected $filename;

    /**
     * Holds the live version string.
     * @var string
     */
    protected $liveVersion;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->filename = $_SERVER['argv'][0];

        $this
            // ->setAliases(array('selfupdate'))
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
        $version = \Codeception\Codecept::VERSION;

        $output->writeln(
            sprintf(
                '<info>%s</info> version <comment>%s</comment>',
                self::NAME, $version
            )
        );

        $output->writeln("\n<info>Checking for a new version...</info>\n");
        try {
            if ($this->isOutOfDate($version)) {
                $output->writeln(
                    sprintf(
                        'A newer version is available: <comment>%s</comment>',
                        $this->liveVersion
                    )
                );
                if (!$input->getOption('no-interaction')) {

                    $dialog = $this->getHelperSet()->get('dialog');
                    if (!$dialog->askConfirmation(
                        $output,
                        "\n<question>Do you want to update?</question> ", false
                    )
                    ) {
                        $output->writeln("\n<info>Bye-bye!</info>\n");

                        return;
                    }
                }
                $output->writeln("\n<info>Updating...</info>");

                $this->retrieveLatestPharFile($output);

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
        }

    }

    /**
     * Checks wether the provided version is current.
     *
     * @param  string $version The version number to check.
     * @return boolean Returns True if a new version is available.
     */
    private function isOutOfDate($version)
    {
        $tags = $this->getGithubTags(self::GITHUB);

        $this->liveVersion = array_reduce(
            $tags, function ($a, $b) {
                return version_compare($a, $b, '>') ? $a : $b;
            }
        );

        return -1 != version_compare($version, $this->liveVersion, '>=');
    }

    /**
     * Returns an array of tags from a github repo.
     *
     * @param  string $repo The repository name to check upon.
     * @return array
     */
    private function getGithubTags($repo)
    {
        $jsonTags = $this->retrieveContentFromUrl(
            'https://api.github.com/repos/' . $repo . '/tags'
        );

        return array_map(
            function ($tag) {
                return $tag['name'];
            },
            json_decode($jsonTags, true)
        );
    }

    /**
     * Retrieves the body-content from the provided URL.
     *
     * @param  string $url
     * @return string
     * @throw Exception if status code is above 300
     */
    private function retrieveContentFromUrl($url)
    {
        $opts = [
            'http' => [
                'follow_location' => 1,
                'max_redirects'   => 20,
                'timeout'         => 60,
                'user_agent'      => self::NAME
            ]
        ];

        $ctx = stream_context_create($opts);
        $body = file_get_contents($url, 0, $ctx);

        if (isset($http_response_header)) {
            $code = substr($http_response_header[0], 9, 3);
            if (floor($code / 100) > 3) {
                throw new \Exception($http_response_header[0]);
            }
        } else {
            throw new \Exception('Request failed.');
        }

        return $body;
    }

    /**
     * Retrieves the latest phar file.
     *
     * @param OutputInterface $output
     */
    protected function retrieveLatestPharFile(OutputInterface $output)
    {
        $temp = basename($this->filename, '.phar') . '-temp.phar';

        try {
            if (@copy(self::SOURCE, $temp)) {
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
            if (
                !$e instanceof \UnexpectedValueException
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

}
