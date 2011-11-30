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

        if (isset($this->config['suites'])) {

            $globalConf = $this->config['settings'];
            $moduleConf = array('modules' => isset($this->config['modules']) ? $this->config['modules'] : array());


            foreach ($this->config['suites'] as $suite) {

                $suiteConf = file_exists($this->tests_path . "/$suite.suite.yml") ? Yaml::parse($this->tests_path . "/$suite.suite.yml") : array();
                $suiteDistconf = file_exists($this->tests_path . "/$suite.suite.dist.yml") ? Yaml::parse($this->tests_path . "/$suite.suite.dist.yml") : array();

                $this->suites[$suite] = array_merge($globalConf, $moduleConf, $suiteDistconf, $suiteConf);
            }
        }
    }

}
