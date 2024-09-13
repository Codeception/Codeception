<?php

declare(strict_types=1);

namespace Codeception\Command\Shared;

use Codeception\Configuration;
use InvalidArgumentException;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

use function array_merge_recursive;
use function array_pop;
use function array_shift;
use function class_exists;
use function count;
use function explode;
use function str_repeat;
use function ucfirst;

trait ConfigTrait
{
    protected function getSuiteConfig(string $suite): array
    {
        return Configuration::suiteSettings($suite, $this->getGlobalConfig());
    }

    protected function getGlobalConfig(?string $conf = null): array
    {
        return Configuration::config($conf);
    }

    /**
     * @return string[]
     */
    protected function getSuites(): array
    {
        return Configuration::suites();
    }

    protected function overrideConfig($configOptions): array
    {
        $updatedConfig = [];
        foreach ($configOptions as $option) {
            $keys = explode(': ', $option);
            if (count($keys) < 2) {
                throw new InvalidArgumentException('--override should have config passed as "key: value"');
            }
            $value = array_pop($keys);
            $yaml = '';
            for ($ind = 0; count($keys); $ind += 2) {
                $yaml .= "\n" . str_repeat(' ', $ind) . array_shift($keys) . ': ';
            }
            $yaml .= $value;
            try {
                $config = Yaml::parse($yaml);
            } catch (ParseException $e) {
                throw new \Codeception\Exception\ParseException("Overridden config can't be parsed: \n{$yaml}\n" . $e->getParsedLine());
            }
            $updatedConfig = array_merge_recursive($updatedConfig, $config);
        }
        return Configuration::append($updatedConfig);
    }

    protected function enableExtensions($extensions): array
    {
        $config = ['extensions' => ['enabled' => []]];
        foreach ($extensions as $name) {
            if (!class_exists($name)) {
                $className = 'Codeception\\Extension\\' . ucfirst($name);
                if (!class_exists($className)) {
                    throw new InvalidOptionException("Extension {$name} can't be loaded (tried by {$name} and {$className})");
                }
                $config['extensions']['enabled'][] = $className;
                continue;
            }
            $config['extensions']['enabled'][] = $name;
        }
        return Configuration::append($config);
    }
}
