<?php

namespace Codeception\Template;

use Codeception\InitTemplate;
use Codeception\Util\Template;
use Symfony\Component\Yaml\Yaml;

class Acceptance extends InitTemplate
{
    protected $configTemplate = <<<EOF
# suite config
suites:
    acceptance:
        actor: AcceptanceTester
        path: .
        modules:
            enabled:
                - WebDriver:
                    url: {{url}}
                    browser: {{browser}}
                - \Helper\Acceptance
                
extensions:
    enabled: [Codeception\Extension\RunFailed]

params: 
    - env

gherkin: []    

# additional paths
paths:
    tests: {{baseDir}}
    output: {{baseDir}}/_output
    data: {{baseDir}}/_data
    support: {{baseDir}}/_support
    envs: {{baseDir}}/_envs

settings:
    shuffle: false
    lint: true
EOF;

    protected $firstTest = <<<EOF
<?php
class LoginCest 
{    
    public function _before(AcceptanceTester \$I)
    {
        \$I->amOnPage('/');
    }

    public function loginSuccessfully(AcceptanceTester \$I)
    {
        // write a positive login test 
    }
    
    public function loginWithInvalidPassword(AcceptanceTester \$I)
    {
        // write a negative login test
    }       
}
EOF;


    public function setup()
    {
        $this->checkInstalled();
        $this->say("Let's prepare Codeception for acceptance testing");
        $this->say("Create your tests and run them in real browser");
        $this->say("");

        $dir = $this->ask("Where tests will be stored?", 'tests');

        $browser = $this->ask("Select a browser for testing", ['chrome', 'phantomjs', 'firefox']);
        if ($browser === 'phantomjs') {
            $this->sayInfo("Ensure that you have Phantomjs running before starting tests");
        }
        if ($browser === 'chrome') {
            $this->sayInfo("Ensure that you have Selenium Server and ChromeDriver installed before running tests");
        }
        if ($browser === 'firefox') {
            $this->sayInfo("Ensure that you have Selenium Server and GeckoDriver installed before running tests");
        }
        $url = $this->ask("Start url for tests", "http://localhost");

        $this->createEmptyDirectory($outputDir = $dir . DIRECTORY_SEPARATOR . '_output');
        $this->createEmptyDirectory($dir . DIRECTORY_SEPARATOR . '_data');
        $this->createDirectoryFor($supportDir = $dir . DIRECTORY_SEPARATOR . '_support');
        $this->createDirectoryFor($supportDir . DIRECTORY_SEPARATOR . '_generated');
        $this->gitIgnore($outputDir);
        $this->gitIgnore($supportDir . DIRECTORY_SEPARATOR . '_generated');
        $this->sayInfo("Created test directories inside at $dir");

        $configFile = (new Template($this->configTemplate))
            ->place('url', $url)
            ->place('browser', $browser)
            ->place('baseDir', $dir)
            ->produce();

        if ($this->namespace) {
            $namespace = rtrim($this->namespace, '\\');
            $configFile = "namespace: $namespace\n" . $configFile;
        }

        $this->createFile('codeception.yml', $configFile);
        $this->createHelper('Acceptance', $supportDir);
        $this->createActor('AcceptanceTester', $supportDir, Yaml::parse($configFile)['suites']['acceptance']);

        $this->sayInfo("Created global config codeception.yml inside the root directory");
        $this->createFile($dir . DIRECTORY_SEPARATOR . 'LoginCest.php', $this->firstTest);
        $this->sayInfo("Created a demo test LoginCest.php");

        $this->say();
        $this->saySuccess("INSTALLATION COMPLETE");

        $this->say();
        $this->say("<bold>Next steps:</bold>");
        $this->say('1. Launch Selenium Server or PhantomJS and webserver');
        $this->say("2. Edit <bold>$dir/LoginCest.php</bold> to test login of your application");
        $this->say("3. Run tests using: <comment>codecept run</comment>");
        $this->say();
        $this->say("<bold>Happy testing!</bold>");
    }
}
