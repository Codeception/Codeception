<?php
namespace Codeception\Command;

use \Symfony\Component\Yaml\Yaml;

class Base extends \Symfony\Component\Console\Command\Command
{
    protected $config;
    protected $suites = array();
    protected $tests_path;

    protected function initCodeception()
    {
        $this->config = \Codeception\Codecept::loadConfiguration();
        $this->tests_path = $this->config['paths']['tests'];
    }

}
