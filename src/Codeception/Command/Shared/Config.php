<?php
namespace Codeception\Command\Shared;

use Codeception\Configuration;
use Symfony\Component\Yaml\Yaml;

trait Config
{
    protected function getSuiteConfig($suite, $conf)
    {
        $config = Configuration::config($conf);
        return Configuration::suiteSettings($suite, $config);
    }

    protected function getGlobalConfig($conf)
    {
        return Configuration::config($conf);
    }

    protected function getSuites($conf)
    {
        Configuration::config($conf);
        return Configuration::suites();
    }

    protected function overrideConfig($configOptions)
    {
        $updatedConfig = [];
        foreach ($configOptions as $option) {
            $keys = explode(':', $option);
            if (count($keys) < 2) {
                throw new \InvalidArgumentException('--config-option should have config passed as "key:value"');
            }
            $value = array_pop($keys);
            $key = implode(":\n  ", $keys);
            $config = Yaml::parse("$key:$value");
            $updatedConfig = array_merge_recursive($updatedConfig, $config);
        }
        return Configuration::append($updatedConfig);
    }
}
