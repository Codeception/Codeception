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
        $this->say();

        $dir     = $this->ask('Where tests will be stored?', 'tests');
        $browser = $this->ask('Select a browser for testing', ['chrome', 'firefox']);
        $this->sayInfo(
            $browser === 'chrome'
                ? 'Ensure Selenium Server and ChromeDriver are installed'
                : 'Ensure Selenium Server and GeckoDriver are installed'
        );
        $url = $this->ask('Start URL for tests', 'http://localhost');

        $paths = [
            '_output',
            'Support',
            'Support/Data',
            'Support/_generated',
        ];
        foreach ($paths as $sub) {
            $full = $dir . DIRECTORY_SEPARATOR . $sub;
            if ($sub === 'Support/Data') {
                $this->createEmptyDirectory($full);
            } elseif (str_ends_with($sub, '_generated')) {
                $this->createEmptyDirectory($full);
                $this->gitIgnore($full);
            } else {
                $this->createDirectoryFor($full);
                if ($sub === '_output') {
                    $this->gitIgnore($full);
                }
            }
        }
        $this->sayInfo("Created test directories at {$dir}");

        if (!class_exists('\\Codeception\\Module\\WebDriver')) {
            $this->addModulesToComposer(['WebDriver']);
        }

        $config = (new Template($this->configTemplate))
            ->place('url', $url)
            ->place('browser', $browser)
            ->place('baseDir', $dir)
            ->produce();

        $namespace = rtrim($this->namespace, '\\');
        $config = "namespace: {$namespace}\nsupport_namespace: {$this->supportNamespace}\n" . $config;
        $this->createFile('codeception.yml', $config);

        $settings = Yaml::parse($config)['suites']['acceptance'];
        $settings['support_namespace'] = $this->supportNamespace;
        $this->createActor('AcceptanceTester', $dir . '/Support', $settings);

        $this->sayInfo('Created global config codeception.yml inside the root directory');

        $firstTest = (new Template($this->firstTest))
            ->place('namespace', $namespace)
            ->place('support_namespace', $this->supportNamespace)
            ->produce();
        $this->createFile($dir . '/LoginCest.php', $firstTest);
        $this->sayInfo('Created a demo test LoginCest.php');
        $this->say();
        $this->saySuccess('INSTALLATION COMPLETE');
        $this->say();
        $this->say('<bold>Next steps:</bold>');
        $this->say('1. Launch Selenium Server and webserver');
        $this->say("2. Edit <bold>{$dir}/LoginCest.php</bold> to test login of your application");
        $this->say("3. Run tests using: <comment>codecept run</comment>");
        $this->say();
        $this->say("HINT: Add '\\Codeception\\Step\\Retry' trait to AcceptanceTester class to enable auto-retries");
        $this->say("HINT: See https://codeception.com/docs/03-AcceptanceTests#retry");
        $this->say('<bold>Happy testing!</bold>');
    }
}
