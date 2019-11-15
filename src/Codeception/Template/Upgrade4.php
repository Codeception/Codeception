<?php

namespace Codeception\Template;

use Codeception\Configuration;
use Codeception\InitTemplate;

class Upgrade4 extends InitTemplate
{
    const SURVEY_LINK = 'http://bit.ly/codecept-survey';
    const DONATE_LINK = 'https://opencollective.com/codeception';

    protected $modules = [
        'WebDriver' => "codeception/module-webdriver",
        'Yii2' => "codeception/module-yii2",
        'Doctrine2' => "codeception/module-doctrine2",
        'Asserts' => 'codeception/module-asserts',
        'Filesystem' => 'codeception/module-filesystem',
        'Cli' => 'codeception/module-cli',
        'PhpBrowser' => 'codeception/module-phpbrowser',
        'Db' => 'codeception/module-db',
        'ZendExpressive' => 'codeception/module-zendexpressive',
        'Symfony' => 'codeception/module-symfony',
        'REST' => 'codeception/module-rest',
        'Lumen' => 'codeception/module-lumen',
        'Laravel5' => 'codeception/module-laravel5',
        'Phalcon' => 'codeception/module-phalcon',
        'ZF2' => 'codeception/module-zf2',
        'Sequence' => 'codeception/module-sequence',
        'SOAP' => 'codeception/module-soap',
        'Redis' => 'codeception/module-redis',
        'Queue' => 'codeception/module-queue',
        'MongoDb' => 'codeception/module-mongodb',
        'Memcache' => 'codeception/module-memcache',
        'FTP' => 'codeception/module-ftp',
        'DataFactory' => 'codeception/module-datafactory',
        'Apc' => 'codeception/module-apc',
        'AMQP' => 'codeception/module-amqp',
        
    ];

    public function setup()
    {
        if (!$this->isInstalled()) {
            $this->sayWarning('Codeception is not installed in this dir.');
            return;
        }
        $this->sayInfo('Welcome to Codeception v4 Upgrade wizard!');
        $this->say('');
        $this->say('Codeception is maintained since 2011, is free & open-source.');
        $this->say('To make it better we need your feedback on it!');
        $this->say('');
        $this->say('Please take a minute and fill in a brief survey:');
        $this->say('<bold>'  . self::SURVEY_LINK . '</bold>');
        $this->say('');
        $result = $this->ask('<question>Did you fill in the survey?</question>', true);
        if ($result) {
            $this->say('Thank you! ');
        } else {
            $this->say('Anyway...');
        }
        if (!file_exists('composer.json')) {
            $this->sayWarning('Please use composer installation of Codeception');
            throw new \Exception('composer.json not found, can\'t run upgrade');
        }
        $composer = json_decode(file_get_contents('composer.json'), true);
        if ($composer === null) {
            throw new \Exception("Invalid composer.json file. JSON can't be decoded");
        }
        $section = null;
        if (isset($composer['require'])) {
            if (isset($composer['require']['codeception/codeception'])) {
                $section = 'require';
            }
        }
        if (isset($composer['require-dev'])) {
            if (isset($composer['require-dev']['codeception/codeception'])) {
                $section = 'require-dev';
            }
        }
        if (!$section) {
            throw new \Exception("No 'codeception/codeception' found in composer.json. Can't upgrade");
        }
        $config = Configuration::config();
        $packageCounter = 0;
        $modules = [];
        foreach (Configuration::suites() as $suite) {
            $suiteConfig = Configuration::suiteSettings($suite, $config);
            $modules = array_merge($modules, Configuration::modules($suiteConfig));
        }

        foreach (array_unique($modules) as $module) {
            if (!isset($this->modules[$module])) continue;
            $package = $this->modules[$module];
            if (isset($composer[$section][$package])) continue;
            $this->sayInfo("Adding $package for $module to composer.json");
            $composer[$section][$package] = "^1.0.0";
            $packageCounter++;
        }

        file_put_contents('composer.json', json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
        $this->sayInfo('Running composer install');
        exec('composer update', $output, $status);
        if ($status !== 0) {
            $this->sayInfo('Composer installation failed. Please check composer.json and try to run "composer install" manually');
            return;
        }

        $this->saySuccess("Done upgrading!");
        if ($packageCounter) {
            $this->say("$packageCounter new packages installed");
        }
        $this->say('');

        $this->say('Please consider donating to Codeception on regular basis:');
        $this->say('');
        $this->say('<bold>' . self::DONATE_LINK . '</bold>');
        $this->say('');
        $this->say('It\'s ok to pay for reliable software.');
        $this->say('Talk to your manager & support further development of Codeception!');

    }

    private function isInstalled()
    {
        try {
            $this->checkInstalled();
        } catch (\Exception $e) {
            return true;
        }
        return false;
    }
}
