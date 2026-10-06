<?php

declare(strict_types=1);

namespace Codeception\Config;

use Codeception\Configuration;
use InvalidArgumentException;

use function array_map;
use function is_array;

/**
 * @internal Base for {@see GlobalConfig} and {@see SuiteConfig}; do not use directly.
 */
abstract class AbstractConfigBuilder implements ConfigInterface
{
    /**
     * @var array<string, mixed>
     */
    protected array $config = [];

    final public function __construct()
    {
    }

    public static function create(): static
    {
        return new static();
    }

    /**
     * Enables a module. Call once per module.
     *
     * @param array<string, mixed>      $config  Module options
     * @param list<string>|string|null  $depends Module(s) this one depends on
     */
    public function module(string $name, array $config = [], array|string|null $depends = null): static
    {
        if ($name === '') {
            throw new InvalidArgumentException('Module name cannot be empty.');
        }
        if ($depends !== null) {
            if (isset($config['depends'])) {
                throw new InvalidArgumentException("Module {$name}: 'depends' given both as argument and in config.");
            }
            $config['depends'] = $depends;
        }
        $this->config['modules']['enabled'][] = $config === [] ? $name : [$name => $config];
        return $this;
    }

    /**
     * Overrides the options of an already-enabled module. Use in env and dist layers.
     *
     * @param array<string, mixed> $config
     */
    public function moduleConfig(string $name, array $config): static
    {
        if ($name === '') {
            throw new InvalidArgumentException('Module name cannot be empty.');
        }
        $this->config['modules']['config'][$name] = $config;
        return $this;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function extension(string $class, array $config = []): static
    {
        $this->config['extensions']['enabled'][] = $class;
        if ($config !== []) {
            $this->config['extensions']['config'][$class] = $config;
        }
        return $this;
    }

    /**
     * @param array<string, string|list<string>> $groups
     */
    public function groups(array $groups): static
    {
        $this->config['groups'] = $groups;
        return $this;
    }

    /**
     * @param array<string, mixed> $coverage
     */
    public function coverage(array $coverage): static
    {
        $this->config['coverage'] = $coverage;
        return $this;
    }

    public function namespace(string $namespace): static
    {
        $this->config['namespace'] = $namespace;
        return $this;
    }

    /**
     * Deep-merges raw config keys for anything without a dedicated method.
     * The argument wins over previously-set keys, like a later chained call.
     * Example: ->merge(['reporters' => ['report' => Custom::class]])
     *
     * @param array<string, mixed> $config
     */
    public function merge(array $config): static
    {
        $this->config = Configuration::mergeConfigs($this->config, $config);
        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return self::normalize($this->config);
    }

    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    private static function normalize(array $config): array
    {
        return array_map(
            static fn (mixed $value): mixed => match (true) {
                $value instanceof ConfigInterface => $value->toArray(),
                is_array($value) => self::normalize($value),
                default => $value,
            },
            $config
        );
    }
}
