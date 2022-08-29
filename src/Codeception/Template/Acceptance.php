<?php

declare(strict_types=1);

namespace Codeception\Template;

use Codeception\InitTemplate;
use Codeception\Util\Template;
use Symfony\Component\Yaml\Yaml;

class Acceptance extends InitTemplate
{
    protected string $configTemplate = <<<EOF
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
                
        # add Codeception\Step\Retry trait to AcceptanceTester to enable retries
        step_decorators:
            - Codeception\Step\ConditionalAssertion
            - Codeception\Step\TryTo
            - Codeception\Step\Retry

extensions:
    enabled: [Codeception\Extension\RunFailed]

params: 
    - env

gherkin: []    

# additional paths
paths:
    tests: {{baseDir}}
    output: {{baseDir}}/_output
    data: {{baseDir}}/Support/Data
    support: {{baseDir}}/Support
    envs: {{baseDir}}/_envs

settings:
    shuffle: false
    lint: true
EOF;

    protected string $firstTest = <<<EOF
<?php

namespace {{namespace}};

use {{namespace}}\{{support_namespace}}\AcceptanceTester;

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

    public function setup(): void
    {
        $this->checkInstalled();
        $this->say("Let's prepare Codeception for acceptance testing");
        $this->say("Create your tests and run them in real browser");
        $this->say('');

        $dir = $this->ask("Where tests will be stored?", 'tests');

        $browser = $this->ask("Select a browser for testing", ['chrome', 'firefox']);
        if ($browser === 'chrome') {
            $this->sayInfo("Ensure that you have Selenium Server and ChromeDriver installed before running tests");
        }
        if ($browser === 'firefox') {
            $this->sayInfo("Ensure that you have Selenium Server and GeckoDriver installed before running tests");
        }
        $url = $this->ask("Start url for tests", "http://localhost");

        $this->createDirectoryFor($outputDir = $dir . DIRECTORY_SEPARATOR . '_output');
        $this->createDirectoryFor($supportDir = $dir . DIRECTORY_SEPARATOR . 'Support');
        $this->createEmptyDirectory($supportDir . DIRECTORY_SEPARATOR . 'Data');
        $this->createDirectoryFor($supportDir . DIRECTORY_SEPARATOR . '_generated');
        $this->gitIgnore($outputDir);
        $this->gitIgnore($supportDir . DIRECTORY_SEPARATOR . '_generated');
        $this->sayInfo("Created test directories inside at {$dir}");

        if (!class_exists('\\Codeception\\Module\\WebDriver')) {
            // composer version
            $this->addModulesToComposer(['WebDriver']);
        }

        $configFile = (new Template($this->configTemplate))
            ->place('url', $url)
            ->place('browser', $browser)
            ->place('baseDir', $dir)
            ->produce();

        $namespace = rtrim($this->namespace, '\\');
        $configFile = "namespace: $namespace\nsupport_namespace: {$this->supportNamespace}\n" . $configFile;

        $this->createFile('codeception.yml', $configFile);

        $settings = Yaml::parse($configFile)['suites']['acceptance'];
        $settings['support_namespace'] = $this->supportNamespace;
        $this->createActor('AcceptanceTester', $supportDir, $settings);

        $this->sayInfo("Created global config codeception.yml inside the root directory");
        $this->createFile(
            $dir . DIRECTORY_SEPARATOR . 'LoginCest.php',
            (new Template($this->firstTest))
                ->place('namespace', $this->namespace)
                ->place('support_namespace', $this->supportNamespace)
                ->produce()
        );
        $this->sayInfo("Created a demo test LoginCest.php");

        $this->say();
        $this->saySuccess("INSTALLATION COMPLETE");

        $this->say();
        $this->say("<bold>Next steps:</bold>");
        $this->say('1. Launch Selenium Server and webserver');
        $this->say("2. Edit <bold>{$dir}/LoginCest.php</bold> to test login of your application");
        $this->say("3. Run tests using: <comment>codecept run</comment>");
        $this->say();
        $this->say("HINT: Add '\\Codeception\\Step\\Retry' trait to AcceptanceTester class to enable auto-retries");
        $this->say("HINT: See https://codeception.com/docs/03-AcceptanceTests#retry");
        $this->say("<bold>Happy testing!</bold>");
    }
}
