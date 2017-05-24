<?php
namespace Codeception\Command\Shared;

use Codeception\Configuration;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

trait Config
{
    protected function getSuiteConfig($suite)
    {
        return Configuration::suiteSettings($suite, $this->getGlobalConfig());
    }

    protected function getGlobalConfig($conf = null)
    {
        return Configuration::config($conf);
    }

    protected function getSuites($conf = null)
    {
        return Configuration::suites();
    }

    protected function parseParams($params)
    {
        $updatedParams = [];

        foreach ($params as $match) {
            $match = explode(',', str_replace(array(', '), ',', $match));

            if (is_array($match) && count($match) > 0) {
                foreach ($match as $k => $param) {
                    $keys = explode(': ', $param);
                    if (count($keys) < 2) {
                        throw new \InvalidArgumentException('--param option should have config passed as "key: value"');
                    }

                    $updatedParams[$keys[0]] = $keys[1];
                }
            }
        }

        return $updatedParams;
    }

    protected function overrideConfig($configOptions)
    {
        $updatedConfig = [];
        foreach ($configOptions as $option) {
            $keys = explode(': ', $option);
            if (count($keys) < 2) {
                throw new \InvalidArgumentException('--config-option should have config passed as "key: value"');
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
