<?php

declare(strict_types=1);

namespace Codeception\Config;

use InvalidArgumentException;

/**
 * Fluent builder for a suite config file (`{Suite}.suite.php`) or an inline
 * {@see GlobalConfig::suite()} definition.
 *
 * <code>
 * use Codeception\Config\SuiteConfig;
 *
 * return SuiteConfig::create()
 *     ->actor('FunctionalTester')
 *     ->module('Symfony', ['app_path' => 'src', 'environment' => 'test'])
 *     ->module('Doctrine', depends: 'Symfony');
 * </code>
 */
final class SuiteConfig extends AbstractConfigBuilder
{
    public function actor(?string $actor): static
    {
        $this->config['actor'] = $actor;
        return $this;
    }

    public function path(?string $path): static
    {
        $this->config['path'] = $path;
        return $this;
    }

    public function extends(?string $configFile): static
    {
        $this->config['extends'] = $configFile;
        return $this;
    }

    /**
     * @param list<class-string>|class-string|null $decorators
     */
    public function stepDecorators(array|string|null $decorators): static
    {
        $this->config['step_decorators'] = $decorators;
        return $this;
    }

    /**
     * @param list<string> $formats
     */
    public function formats(array $formats): static
    {
        $this->config['formats'] = $formats;
        return $this;
    }

    public function shuffle(bool $shuffle = true): static
    {
        $this->config['shuffle'] = $shuffle;
        return $this;
    }

    /**
     * Native constants work here: ->errorLevel(E_ALL & ~E_DEPRECATED)
     */
    public function errorLevel(int|string $errorLevel): static
    {
        $this->config['error_level'] = $errorLevel;
        return $this;
    }

    public function convertDeprecationsToExceptions(bool $convert = true): static
    {
        $this->config['convert_deprecations_to_exceptions'] = $convert;
        return $this;
    }

    /**
     * Adds an environment overlay for this suite (merged when run with `--env {name}`).
     *
     * @param SuiteConfig|array<string, mixed> $overrides
     */
    public function env(string $name, SuiteConfig|array $overrides): static
    {
        if ($name === '') {
            throw new InvalidArgumentException('Environment name cannot be empty.');
        }
        $this->config['env'][$name] = $overrides;
        return $this;
    }
}
