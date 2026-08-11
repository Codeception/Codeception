<?php

declare(strict_types=1);

namespace Codeception\Lib\Generator;

use function array_diff;
use function array_filter;
use function array_is_list;
use function array_keys;
use function array_map;
use function array_push;
use function implode;
use function in_array;
use function is_array;
use function is_bool;
use function is_int;
use function preg_match;
use function sprintf;
use function var_export;

/**
 * Renders a config array as builder-style PHP source for `config:to-php` and tests.
 * Top-level structure becomes {@see \Codeception\Config\GlobalConfig}/{@see \Codeception\Config\SuiteConfig}
 * method calls; leaf option arrays stay as array literals. Whole-value `%param%` placeholders become
 * getenv() (global) or Params::get() (suite); embedded placeholders are kept verbatim and reported.
 */
final class PhpConfigFile
{
    private const GLOBAL = 'global';
    private const SUITE  = 'suite';

    private const GLOBAL_ONLY = [
        'support_namespace', 'actor_suffix', 'bootstrap', 'paths', 'settings',
        'params', 'include', 'gherkin', 'suites',
    ];

    private const PATH_KEYS = ['tests', 'output', 'data', 'support', 'envs'];

    private const SUITE_ONLY = [
        'actor', 'path', 'formats', 'step_decorators', 'shuffle', 'error_level',
        'convert_deprecations_to_exceptions', 'env',
    ];

    private const SIMPLE_CALLS = [
        'namespace'         => 'namespace',
        'support_namespace' => 'supportNamespace',
        'actor_suffix'      => 'actorSuffix',
        'actor'             => 'actor',
        'path'              => 'path',
        'bootstrap'         => 'bootstrap',
        'extends'           => 'extends',
        'gherkin'           => 'gherkin',
        'groups'            => 'groups',
        'coverage'          => 'coverage',
        'formats'           => 'formats',
        'step_decorators'   => 'stepDecorators',
        'shuffle'           => 'shuffle',
        'error_level'       => 'errorLevel',
        'convert_deprecations_to_exceptions' => 'convertDeprecationsToExceptions',
    ];

    private const VARIADIC_CALLS = [
        'include' => 'include',
        'params'  => 'params',
    ];

    private const SETTINGS_MAP = [
        'shuffle'                                 => 'shuffle',
        'colors'                                  => 'colors',
        'lint'                                    => 'lint',
        'bootstrap'                               => 'bootstrap',
        'memory_limit'                            => 'memoryLimit',
        'backup_globals'                          => 'backupGlobals',
        'report_useless_tests'                    => 'reportUselessTests',
        'strict_xml'                              => 'strictXml',
        'be_strict_about_changes_to_global_state' => 'beStrictAboutChangesToGlobalState',
    ];

    /** @var list<string> */
    private array $warnings = [];

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }

    /**
     * @param array<string, mixed> $config
     */
    public function renderGlobal(array $config): string
    {
        $usesSuite = isset($config['suites']) && $config['suites'] !== [];
        $use = "use Codeception\\Config\\GlobalConfig;\n";
        if ($usesSuite) {
            $use .= "use Codeception\\Config\\SuiteConfig;\n";
        }
        return $this->wrap('GlobalConfig', $use, $this->buildCalls($config, self::GLOBAL));
    }

    /**
     * @param array<string, mixed> $config
     */
    public function renderSuite(array $config): string
    {
        return $this->wrap('SuiteConfig', "use Codeception\\Config\\SuiteConfig;\n", $this->buildCalls($config, self::SUITE));
    }

    /**
     * @param list<string> $calls
     */
    private function wrap(string $class, string $use, array $calls): string
    {
        $chain = $calls === [] ? '' : "\n    " . implode("\n    ", $calls);
        return "<?php\n\ndeclare(strict_types=1);\n\n{$use}\nreturn {$class}::create(){$chain};\n";
    }

    /**
     * @param array<string, mixed> $config
     * @return list<string>
     */
    private function buildCalls(array $config, string $mode): array
    {
        $calls  = [];
        $merge  = [];
        foreach ($config as $key => $value) {
            $call = $this->callFor($key, $value, $mode);
            if ($call === null) {
                $merge[$key] = $value;
            } else {
                array_push($calls, ...$call);
            }
        }
        if ($merge !== []) {
            $calls[] = '->merge(' . $this->export($merge, $mode) . ')';
        }
        return $calls;
    }

    /**
     * @return list<string>|null Null routes the key to ->merge().
     */
    private function callFor(string $key, mixed $value, string $mode): ?array
    {
        if ($mode === self::SUITE && in_array($key, self::GLOBAL_ONLY, true)) {
            return null;
        }
        if ($mode === self::GLOBAL && in_array($key, self::SUITE_ONLY, true)) {
            return null;
        }

        if (isset(self::SIMPLE_CALLS[$key])) {
            return [sprintf('->%s(%s)', self::SIMPLE_CALLS[$key], $this->export($value, $mode))];
        }
        if (isset(self::VARIADIC_CALLS[$key])) {
            return [sprintf('->%s(%s)', self::VARIADIC_CALLS[$key], $this->args($value, $mode))];
        }

        return match ($key) {
            'paths'      => $this->pathsCall($value, $mode),
            'settings'   => $this->settingsCall($value, $mode),
            'modules'    => $this->modulesCalls($value, $mode),
            'extensions' => $this->extensionsCalls($value, $mode),
            'suites'     => $this->suitesCalls($value),
            'env'        => $this->envCalls($value, $mode),
            default      => null,
        };
    }

    /**
     * @return list<string>|null
     */
    private function pathsCall(mixed $paths, string $mode): ?array
    {
        if (!is_array($paths) || array_diff(array_keys($paths), self::PATH_KEYS) !== []) {
            return null;
        }
        $args = [];
        foreach (self::PATH_KEYS as $k) {
            if (isset($paths[$k])) {
                $args[] = sprintf('%s: %s', $k, $this->export($paths[$k], $mode));
            }
        }
        return $args === [] ? null : [sprintf('->paths(%s)', implode(', ', $args))];
    }

    /**
     * @return list<string>|null
     */
    private function settingsCall(mixed $settings, string $mode): ?array
    {
        if (!is_array($settings)) {
            return null;
        }
        $args   = [];
        $merge  = [];
        foreach ($settings as $k => $v) {
            if (isset(self::SETTINGS_MAP[$k])) {
                $args[] = sprintf('%s: %s', self::SETTINGS_MAP[$k], $this->export($v, $mode));
            } else {
                $merge[$k] = $v;
            }
        }
        $calls = [];
        if ($args !== []) {
            $calls[] = sprintf('->settings(%s)', implode(', ', $args));
        }
        if ($merge !== []) {
            $calls[] = sprintf('->merge(%s)', $this->export(['settings' => $merge], $mode));
        }
        return $calls === [] ? null : $calls;
    }

    /**
     * @return list<string>|null
     */
    private function modulesCalls(mixed $modules, string $mode): ?array
    {
        if (!is_array($modules)) {
            return null;
        }
        $calls = [];
        foreach ($modules['enabled'] ?? [] as $entry) {
            if (is_array($entry)) {
                foreach ($entry as $name => $conf) {
                    $calls[] = is_array($conf) && $conf !== []
                        ? sprintf('->module(%s, %s)', $this->export((string) $name, $mode), $this->export($conf, $mode))
                        : sprintf('->module(%s)', $this->export((string) $name, $mode));
                }
            } else {
                $calls[] = sprintf('->module(%s)', $this->export($entry, $mode));
            }
        }
        foreach ($modules['config'] ?? [] as $name => $conf) {
            $calls[] = sprintf('->moduleConfig(%s, %s)', $this->export((string) $name, $mode), $this->export($conf, $mode));
        }
        $leftover = array_filter(
            $modules,
            static fn (mixed $value, string $key): bool =>
                !in_array($key, ['enabled', 'config'], true) && $value !== [] && $value !== null,
            ARRAY_FILTER_USE_BOTH
        );
        if ($leftover !== []) {
            $calls[] = sprintf('->merge(%s)', $this->export(['modules' => $leftover], $mode));
        }
        return $calls === [] ? null : $calls;
    }

    /**
     * @return list<string>|null
     */
    private function extensionsCalls(mixed $extensions, string $mode): ?array
    {
        if (!is_array($extensions)) {
            return null;
        }
        $calls   = [];
        $config  = $extensions['config'] ?? [];
        foreach ($extensions['enabled'] ?? [] as $class) {
            if (isset($config[$class])) {
                $calls[] = sprintf('->extension(%s, %s)', $this->export($class, $mode), $this->export($config[$class], $mode));
                unset($config[$class]);
            } else {
                $calls[] = sprintf('->extension(%s)', $this->export($class, $mode));
            }
        }
        if ($mode === self::GLOBAL && !empty($extensions['commands'])) {
            $calls[] = sprintf('->commands(%s)', $this->args($extensions['commands'], $mode));
        }
        $leftover = [];
        if ($config !== []) {
            $leftover['config'] = $config;
        }
        if ($mode !== self::GLOBAL && !empty($extensions['commands'])) {
            $leftover['commands'] = $extensions['commands'];
        }
        if ($leftover !== []) {
            $calls[] = sprintf('->merge(%s)', $this->export(['extensions' => $leftover], $mode));
        }
        return $calls === [] ? null : $calls;
    }

    /**
     * @return list<string>
     */
    private function suitesCalls(mixed $suites): array
    {
        $calls = [];
        foreach ((array) $suites as $name => $conf) {
            $inner = is_array($conf) ? $this->buildCalls($conf, self::SUITE) : [];
            $chain = 'SuiteConfig::create()' . implode('', array_map(static fn (string $c): string => "\n        {$c}", $inner));
            $calls[] = sprintf('->suite(%s, %s)', $this->export((string) $name, self::GLOBAL), $chain);
        }
        return $calls;
    }

    /**
     * @return list<string>|null
     */
    private function envCalls(mixed $env, string $mode): ?array
    {
        if (!is_array($env)) {
            return null;
        }
        $calls = [];
        foreach ($env as $name => $overrides) {
            $calls[] = sprintf('->env(%s, %s)', $this->export((string) $name, $mode), $this->export($overrides, $mode));
        }
        return $calls === [] ? null : $calls;
    }

    private function args(mixed $value, string $mode): string
    {
        $items = array_map(fn (mixed $v): string => $this->export($v, $mode), (array) $value);
        return implode(', ', $items);
    }

    private function export(mixed $value, string $mode): string
    {
        if ($value === null || is_bool($value) || is_int($value)) {
            return var_export($value, true);
        }
        if (is_array($value)) {
            return $this->exportArray($value, $mode);
        }
        if (is_string($value)) {
            if (preg_match('/^%([A-Za-z_][A-Za-z0-9_]*)%$/', $value, $m) === 1) {
                if ($mode === self::GLOBAL) {
                    $this->warnings[] = sprintf(
                        'Whole-value param "%s" became getenv(\'%s\'); getenv() reads real environment '
                        . 'variables only, not param-file values — verify it resolves.',
                        $value,
                        $m[1]
                    );
                    return sprintf("getenv('%s')", $m[1]);
                }
                return sprintf("\\Codeception\\Config\\Params::get('%s')", $m[1]);
            }
            if (preg_match('/%[\w.]+%/', $value) === 1) {
                $this->warnings[] = sprintf(
                    'Param placeholder kept verbatim: "%s" — migrate manually (embedded or unsupported param name).',
                    $value
                );
            }
        }
        return var_export($value, true);
    }

    /**
     * @param array<array-key, mixed> $value
     */
    private function exportArray(array $value, string $mode): string
    {
        if ($value === []) {
            return '[]';
        }
        $isList = array_is_list($value);
        $parts  = [];
        foreach ($value as $k => $v) {
            $parts[] = $isList
                ? $this->export($v, $mode)
                : sprintf('%s => %s', var_export($k, true), $this->export($v, $mode));
        }
        return '[' . implode(', ', $parts) . ']';
    }
}
