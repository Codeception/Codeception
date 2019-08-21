<?php
namespace Codeception\Command;

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
    const PHAR_URL = 'http://codeception.com/releases/%s/codecept.phar';
    const PHAR_URL_PHP54 = 'http://codeception.com/releases/%s/php54/codecept.phar';

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
        if (isset($_SERVER['argv'][0])) {
            $this->filename = $_SERVER['argv'][0];
        } else {
            $this->filename = \Phar::running(false);
        }

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

        $output->writeln("\n<info>Checking for a new version...</info>\n");
        try {
            $latestVersion = $this->getLatestStableVersion();
            if ($this->isOutOfDate($version, $latestVersion)) {
                $output->writeln(
                    sprintf(
                        'A newer version is available: <comment>%s</comment>',
                        $latestVersion
                    )
                );
                if (!$input->getOption('no-interaction')) {
                    $dialog = $this->getHelperSet()->get('question');
                    $question = new ConfirmationQuestion("\n<question>Do you want to update?</question> ", false);
                    if (!$dialog->ask($input, $output, $question)) {
                        $output->writeln("\n<info>Bye-bye!</info>\n");

                        return;
                    }
                }
                $output->writeln("\n<info>Updating...</info>");

                $this->retrievePharFile($latestVersion, $output);
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
     * Checks whether the provided version is current.
     *
     * @param string $version The version number to check.
     * @param string $latestVersion Latest stable version
     * @return boolean Returns True if a new version is available.
     */
    private function isOutOfDate($version, $latestVersion)
    {
        return -1 != version_compare($version, $latestVersion, '>=');
    }

    /**
     * @return string
     */
    private function getLatestStableVersion()
    {
        $stableVersions = $this->filterStableVersions(
            $this->getGithubTags(self::GITHUB_REPO)
        );

        return array_reduce(
            $stableVersions,
            function ($a, $b) {
                return version_compare($a, $b, '>') ? $a : $b;
            }
        );
    }

    /**
     * @param array $tags
     * @return array
     */
    private function filterStableVersions($tags)
    {
        return array_filter($tags, function ($tag) {
            return preg_match('/^[0-9]+\.[0-9]+\.[0-9]+$/', $tag);
        });
    }

    /**
     * Returns an array of tags from a github repo.
     *
     * @param  string $repo The repository name to check upon.
     * @return array
     */
    protected function getGithubTags($repo)
    {
        $jsonTags = $this->retrieveContentFromUrl(
            'https://api.github.com/repos/' . $repo . '/tags'
        );

        return array_column(json_decode($jsonTags, true), 'name');
    }

    /**
     * Retrieves the body-content from the provided URL.
     *
     * @param  string $url
     * @return string
     * @throws \Exception if status code is above 300
     */
    private function retrieveContentFromUrl($url)
    {
        $ctx = $this->prepareContext($url);

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
     * Add proxy support to context if environment variable was set up
     *
     * @param array $opt context options
     * @param string $url
     */
    private function prepareProxy(&$opt, $url)
    {
        $scheme = parse_url($url)['scheme'];
        if ($scheme === 'http' && (!empty($_SERVER['HTTP_PROXY']) || !empty($_SERVER['http_proxy']))) {
            $proxy = !empty($_SERVER['http_proxy']) ? $_SERVER['http_proxy'] : $_SERVER['HTTP_PROXY'];
        }

        if ($scheme === 'https' && (!empty($_SERVER['HTTPS_PROXY']) || !empty($_SERVER['https_proxy']))) {
            $proxy = !empty($_SERVER['https_proxy']) ? $_SERVER['https_proxy'] : $_SERVER['HTTPS_PROXY'];
        }

        if (!empty($proxy)) {
            $proxy = str_replace(['http://', 'https://'], ['tcp://', 'ssl://'], $proxy);
            $opt['http']['proxy'] = $proxy;
        }
    }

    /**
     * Preparing context for request
     * @param $url
     *
     * @return resource
     */
    private function prepareContext($url)
    {
        $opts = [
            'http' => [
                'follow_location' => 1,
                'max_redirects'   => 20,
                'timeout'         => 10,
                'user_agent'      => self::NAME
            ]
        ];
        $this->prepareProxy($opts, $url);
        return stream_context_create($opts);
    }

    /**
     * Retrieves the latest phar file.
     *
     * @param string $version
     * @param OutputInterface $output
     * @throws \Exception
     */
    protected function retrievePharFile($version, OutputInterface $output)
    {
        $temp = basename($this->filename, '.phar') . '-temp.phar';

        try {
            $sourceUrl = $this->getPharUrl($version);
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
     * Returns Phar file URL for specified version
     *
     * @param string $version
     * @return string
     */
    protected function getPharUrl($version)
    {
        $sourceUrl = self::PHAR_URL;
        if (version_compare(PHP_VERSION, '7.0.0', '<')) {
            $sourceUrl = self::PHAR_URL_PHP54;
        }

        return sprintf($sourceUrl, $version);
    }
}
