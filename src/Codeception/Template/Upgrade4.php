<?php
namespace Codeception\Template;

use Codeception\Configuration;
use Codeception\InitTemplate;

class Upgrade4 extends InitTemplate
{
    const SURVEY_LINK = 'http://bit.ly/codecept-survey';
    const DONATE_LINK = 'https://opencollective.com/codeception';

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
        sleep(5);
        $this->say('');
        $result = $this->ask('<question>Did you fill in the survey?</question>', true);
        if ($result) {
            $this->say('Thank you! ');
        } else {
            $this->say('Anyway...');
        }
        $config = Configuration::config();
        $modules = [];
        $suites = Configuration::suites();
        if (empty($suites)) {
            throw new \Exception("No suites found in codeception.yml. Use upgrade script on config with suites");
        }
        foreach (Configuration::suites() as $suite) {
            $suiteConfig = Configuration::suiteSettings($suite, $config);
            $modules = array_merge($modules, Configuration::modules($suiteConfig));
        }

        $this->addModulesToComposer($modules);

        $this->saySuccess("Done upgrading!");
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
