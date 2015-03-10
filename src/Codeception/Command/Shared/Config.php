<?php
namespace Codeception\Command\Shared;

use Codeception\Configuration;

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

} 