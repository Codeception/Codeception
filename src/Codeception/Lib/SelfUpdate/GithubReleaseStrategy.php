<?php

declare(strict_types=1);

namespace Codeception\Lib\SelfUpdate;

use Composer\Semver\Semver;
use Humbug\SelfUpdate\Exception\HttpRequestException;
use Humbug\SelfUpdate\Strategy\StrategyInterface;
use Humbug\SelfUpdate\Updater;
use Humbug\SelfUpdate\VersionParser;

use function file_get_contents;
use function file_put_contents;
use function function_exists;
use function hash;
use function hash_equals;
use function json_decode;
use function preg_match;
use function restore_error_handler;
use function set_error_handler;
use function sprintf;
use function str_starts_with;
use function stream_context_create;
use function strstr;
use function strtolower;
use function trim;
use function version_compare;

/**
 * Updates the phar to the latest stable release of the same major version which supports the running PHP version.
 * Released versions are read from Packagist, the phar is downloaded from the GitHub release.
 *
 * @internal
 */
final class GithubReleaseStrategy implements StrategyInterface
{
    /**
     * @var string
     */
    private const PACKAGIST_URL = 'https://repo.packagist.org/p2/codeception/codeception.json';

    /**
     * @var string
     */
    private const DOWNLOAD_URL = 'https://github.com/Codeception/Codeception/releases/download/%s/%s';

    private ?string $remoteVersion = null;

    private ?string $remoteChecksum = null;

    public function __construct(
        private readonly string $localVersion,
        private readonly string $phpVersion = PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION . '.' . PHP_RELEASE_VERSION
    ) {
    }

    public function download(Updater $updater): void
    {
        [$status, $phar] = $this->request($updater, sprintf(self::DOWNLOAD_URL, $this->remoteVersion, 'codecept.phar'));
        if ($status !== 200) {
            throw new HttpRequestException(sprintf('Downloading codecept.phar %s failed with HTTP status %d', $this->remoteVersion, $status));
        }
        if (!hash_equals((string)$this->remoteChecksum, hash('sha256', $phar))) {
            throw new HttpRequestException(sprintf('Downloaded codecept.phar %s does not match its SHA-256 checksum', $this->remoteVersion));
        }

        file_put_contents($updater->getTempPharFile(), $phar);
    }

    /**
     * Returns the newest compatible release which has a phar attached, or the local version if there is none.
     */
    public function getCurrentRemoteVersion(Updater $updater): string
    {
        [$status, $json] = $this->request($updater, self::PACKAGIST_URL);
        if ($status !== 200) {
            throw new HttpRequestException(sprintf('Request to %s failed with HTTP status %d', self::PACKAGIST_URL, $status));
        }
        $package = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        $releases = self::expandMinified($package['packages']['codeception/codeception'] ?? []);

        foreach (self::findUpdates($releases, $this->localVersion, $this->phpVersion) as $version) {
            $checksumUrl = sprintf(self::DOWNLOAD_URL, $version, 'codecept.phar.sha256');
            [$status, $checksum] = $this->request($updater, $checksumUrl);
            // releases without a phar (e.g. while the phar workflow is still running) are skipped
            if ($status === 404) {
                continue;
            }
            if ($status !== 200) {
                throw new HttpRequestException(sprintf('Request to %s failed with HTTP status %d', $checksumUrl, $status));
            }
            if (!preg_match('/^[0-9a-f]{64}\b/', strtolower(trim($checksum)), $matches)) {
                throw new HttpRequestException(sprintf('%s does not contain a SHA-256 checksum', $checksumUrl));
            }

            $this->remoteChecksum = $matches[0];
            $this->remoteVersion = $version;

            return $version;
        }

        return $this->localVersion;
    }

    public function getCurrentLocalVersion(Updater $updater): string
    {
        return $this->localVersion;
    }

    /**
     * Returns the stable releases newer than $localVersion, of the same major version
     * and supporting $phpVersion, newest first.
     *
     * @param array<array{version: string, require?: array<string, string>}> $releases
     * @return string[]
     */
    public static function findUpdates(array $releases, string $localVersion, string $phpVersion): array
    {
        $major = strstr($localVersion, '.', true) . '.';
        $versionParser = new VersionParser();

        $versions = [];
        foreach ($releases as $release) {
            $version = $release['version'];
            if (
                str_starts_with($version, $major)
                && $versionParser->isStable($version)
                && version_compare($version, $localVersion, '>')
                && Semver::satisfies($phpVersion, $release['require']['php'] ?? '*')
            ) {
                $versions[] = $version;
            }
        }

        return Semver::rsort($versions);
    }

    /**
     * Packagist's p2 metadata only lists the fields which changed from the previous entry,
     * see Composer\MetadataMinifier\MetadataMinifier::expand().
     *
     * @param array<array<string, mixed>> $versions
     * @return array<array<string, mixed>>
     */
    public static function expandMinified(array $versions): array
    {
        $expanded = [];
        $current = [];
        foreach ($versions as $version) {
            foreach ($version as $key => $value) {
                if ($value === '__unset') {
                    unset($current[$key]);
                } else {
                    $current[$key] = $value;
                }
            }
            $expanded[] = $current;
        }

        return $expanded;
    }

    /**
     * @return array{int, string} HTTP status and body of the final response
     */
    private function request(Updater $updater, string $url): array
    {
        $context = stream_context_create(['http' => ['ignore_errors' => true]]);

        set_error_handler([$updater, 'throwHttpRequestException']);
        try {
            $body = file_get_contents($url, false, $context);
            // $http_response_header is deprecated as of PHP 8.5
            if (function_exists('http_get_last_response_headers')) {
                $http_response_header = http_get_last_response_headers();
            }
        } finally {
            restore_error_handler();
        }
        if ($body === false) {
            throw new HttpRequestException(sprintf('Request to URL failed: %s', $url));
        }

        // after redirects, the headers of all responses are listed, the last status line is the final one
        $status = 0;
        foreach ($http_response_header ?? [] as $header) {
            if (preg_match('#^HTTP/\S+\s+(\d{3})#', $header, $matches)) {
                $status = (int)$matches[1];
            }
        }

        return [$status, $body];
    }
}
