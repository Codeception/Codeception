<?php
namespace Codeception\Command\Shared;

use Codeception\Configuration;
use Symfony\Component\Yaml\Exception\ParseException;
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
            $keys = explode(': ', $option);
            if (count($keys) < 2) {
                throw new \InvalidArgumentException('--config-option should have config passed as "key:value"');
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
                throw new \Codeception\Exception\ParseException("Overridden config can't be parsed: \n$yaml\n" . $e->getParsedLine());
            }
            $updatedConfig = array_merge_recursive($updatedConfig, $config);
        }
        return Configuration::append($updatedConfig);
    }
}
