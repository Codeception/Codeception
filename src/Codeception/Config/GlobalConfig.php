<?php

declare(strict_types=1);

namespace Codeception\Config;

use InvalidArgumentException;

use function array_values;

/**
 * Fluent builder for the global config file (`codeception.php`).
 *
 * <code>
 * use Codeception\Config\GlobalConfig;
 * use Codeception\Extension\RunFailed;
 *
 * return GlobalConfig::create()
 *     ->namespace('App\Tests')
 *     ->paths(tests: 'tests', output: 'tests/_output', support: 'tests/Support')
 *     ->extension(RunFailed::class)
 *     ->suite('Unit', SuiteConfig::create()->actor('UnitTester')->module('Asserts'));
 * </code>
 *
 * Notes:
 * - Builders are mutable; a shared "base" builder reused for two suites mutates both.
 *   Return a fresh builder from a function to make a preset.
 * - `%param%` placeholders are a YAML-only feature. In PHP configs use getenv() (global
 *   file) or {@see Params}::get() (suite and env files). Keys without a method go through
 *   {@see AbstractConfigBuilder::merge()}.
 */
final class GlobalConfig extends AbstractConfigBuilder
{
    public function supportNamespace(?string $namespace): static
    {
        $this->config['support_namespace'] = $namespace;
        return $this;
    }

    public function actorSuffix(string $suffix): static
    {
        $this->config['actor_suffix'] = $suffix;
        return $this;
    }

    /**
     * Global bootstrap FILE, loaded once before any suite runs.
     * (The per-suite bootstrap toggle is the `bootstrap:` argument of {@see self::settings()}.)
     */
    public function bootstrap(string|false $bootstrap): static
    {
        $this->config['bootstrap'] = $bootstrap;
        return $this;
    }

    public function extends(string $configFile): static
    {
        $this->config['extends'] = $configFile;
        return $this;
    }

    public function paths(
        ?string $tests = null,
        ?string $output = null,
        ?string $data = null,
        ?string $support = null,
        ?string $envs = null,
    ): static {
        foreach (['tests' => $tests, 'output' => $output, 'data' => $data, 'support' => $support, 'envs' => $envs] as $key => $value) {
            if ($value !== null) {
                $this->config['paths'][$key] = $value;
            }
        }
        return $this;
    }

    /**
     * Run settings. Only the arguments you pass are written.
     *
     * @param string|false|null $bootstrap Per-suite bootstrap file name (not the global bootstrap()).
     * @param int|string|null   $memoryLimit e.g. 1024 or '1G'
     */
    public function settings(
        ?bool $shuffle = null,
        ?bool $colors = null,
        ?bool $lint = null,
        string|false|null $bootstrap = null,
        int|string|null $memoryLimit = null,
        ?bool $backupGlobals = null,
        ?bool $reportUselessTests = null,
        ?bool $strictXml = null,
        ?bool $beStrictAboutChangesToGlobalState = null,
    ): static {
        $map = [
            'shuffle'                                 => $shuffle,
            'colors'                                  => $colors,
            'lint'                                    => $lint,
            'bootstrap'                               => $bootstrap,
            'memory_limit'                            => $memoryLimit,
            'backup_globals'                          => $backupGlobals,
            'report_useless_tests'                    => $reportUselessTests,
            'strict_xml'                              => $strictXml,
            'be_strict_about_changes_to_global_state' => $beStrictAboutChangesToGlobalState,
        ];
        foreach ($map as $key => $value) {
            if ($value !== null) {
                $this->config['settings'][$key] = $value;
            }
        }
        return $this;
    }

    /**
     * @param array<string, mixed>|string ...$sources Param file paths, 'env', or inline maps.
     */
    public function params(array|string ...$sources): static
    {
        $this->config['params'] = array_values($sources);
        return $this;
    }

    public function include(string ...$paths): static
    {
        $this->config['include'] = array_values($paths);
        return $this;
    }

    /**
     * @param array<string, mixed> $gherkin
     */
    public function gherkin(array $gherkin): static
    {
        $this->config['gherkin'] = $gherkin;
        return $this;
    }

    /**
     * Defines a suite inline (single-file config). An inline suite fully replaces a
     * matching `{name}.suite.php`/`.yml` file on disk.
     *
     * @param SuiteConfig|array<string, mixed> $config
     */
    public function suite(string $name, SuiteConfig|array $config): static
    {
        if ($name === '') {
            throw new InvalidArgumentException('Suite name cannot be empty.');
        }
        $this->config['suites'][$name] = $config;
        return $this;
    }

    /**
     * Registers custom console commands (`extensions.commands`).
     *
     * @param class-string ...$commandClasses
     */
    public function commands(string ...$commandClasses): static
    {
        foreach ($commandClasses as $class) {
            $this->config['extensions']['commands'][] = $class;
        }
        return $this;
    }
}
