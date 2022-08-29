<?php

declare(strict_types=1);

namespace Codeception\Template;

use Codeception\InitTemplate;
use Codeception\Module\PhpBrowser;
use Codeception\Util\Template;
use Symfony\Component\Yaml\Yaml;

class Api extends InitTemplate
{
    protected string $configTemplate = <<<EOF
# suite config
suites:
    api:
        actor: ApiTester
        path: .
        modules:
            enabled:
                - REST:
                    url: {{url}}
                    depends: PhpBrowser
        step_decorators:
            - \Codeception\Step\AsJson

paths:
    tests: {{baseDir}}
    output: {{baseDir}}/_output
    data: {{baseDir}}/Support/Data
    support: {{baseDir}}/Support

settings:
    shuffle: false
    lint: true
EOF;

    protected string $firstTest = <<<EOF
<?php
namespace {{namespace}};

use {{namespace}}\{{support_namespace}}\ApiTester;

class ApiCest 
{    
    public function tryApi(ApiTester \$I)
    {
        \$I->sendGet('/');
        \$I->seeResponseCodeIs(200);
        \$I->seeResponseIsJson();
    }
}
EOF;

    public function setup(): void
    {
        $this->checkInstalled();
        $this->say("Let's prepare Codeception for REST API testing");
        $this->say('');

        $dir = $this->ask("Where tests will be stored?", 'tests');

        $url = $this->ask("Start url for tests", "http://localhost/api");

        if (!class_exists('\\Codeception\\Module\\REST') || !class_exists(PhpBrowser::class)) {
            $this->addModulesToComposer(['REST', 'PhpBrowser']);
        }

        $this->createDirectoryFor($outputDir = $dir . DIRECTORY_SEPARATOR . '_output');
        $this->createDirectoryFor($supportDir = $dir . DIRECTORY_SEPARATOR . 'Support');
        $this->createEmptyDirectory($supportDir . DIRECTORY_SEPARATOR . 'Data');
        $this->createDirectoryFor($supportDir . DIRECTORY_SEPARATOR . '_generated');
        $this->gitIgnore($outputDir);
        $this->gitIgnore($supportDir . DIRECTORY_SEPARATOR . '_generated');
        $this->sayInfo("Created test directories inside at {$dir}");

        $configFile = (new Template($this->configTemplate))
            ->place('url', $url)
            ->place('baseDir', $dir)
            ->produce();

        $namespace = rtrim($this->namespace, '\\');
        $configFile = "namespace: $namespace\nsupport_namespace: {$this->supportNamespace}\n" . $configFile;

        $this->createFile('codeception.yml', $configFile);
        $settings = Yaml::parse($configFile)['suites']['api'];
        $settings['support_namespace'] = $this->supportNamespace;
        $this->createActor('ApiTester', $supportDir, $settings);

        $this->sayInfo("Created global config codeception.yml inside the root directory");

        $this->createFile(
            $dir . DIRECTORY_SEPARATOR . 'ApiCest.php',
            (new Template($this->firstTest))
                ->place('namespace', $this->namespace)
                ->place('support_namespace', $this->supportNamespace)
                ->produce()
        );

        $this->sayInfo("Created a demo test ApiCest.php");

        $this->say();
        $this->saySuccess("INSTALLATION COMPLETE");

        $this->say();
        $this->say("<bold>Next steps:</bold>");
        $this->say("1. Edit <bold>{$dir}/ApiCest.php</bold> to write first API tests");
        $this->say("2. Run tests using: <comment>codecept run</comment>");
        $this->say();
        $this->say("<bold>Happy testing!</bold>");
    }
}
