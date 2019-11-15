<?php

namespace Codeception\Template;

use Codeception\Configuration;
use Codeception\InitTemplate;

class Upgrade4 extends InitTemplate
{
    const SURVEY_LINK = 'http://bit.ly/codecept-survey';
    const DONATE_LINK = 'https://opencollective.com/codeception';

    protected $modules = [
      'WebDriver' => 'codeception/module-webdriver',
      'Yii2' => 'codeception/module-yii2',
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
            $this->sayWarning('composer.json not found, can\'t run upgrade');
            $this->sayWarning('Please use composer installation of Codeception');
            return;
        }

        $composer = json_decode(file_get_contents('composer.json'), true);
        if (!$composer['require-dev']) $composer['require-dev'] = [];
        $config = Configuration::config();
        $packageCounter = 0;
        foreach (Configuration::suites() as $suite) {
            $suiteConfig = Configuration::suiteSettings($suite, $config);
            $modules = Configuration::modules($suiteConfig);

            foreach ($modules as $module) {
                if (!isset($this->modules[$module])) continue;
                $package = $this->modules[$module];
                $this->sayInfo("Adding $package for $module to composer.json");
                $composer['require-dev'][$package] = "^1.0.0";
                $packageCounter++;
            }
        }
        file_put_contents('composer.json', json_encode($composer));
        $this->sayInfo('Running composer install');
        exec('composer install', $output, $status);
        $this->sayInfo($output);
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
