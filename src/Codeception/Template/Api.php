<?php

namespace Codeception\Template;

use Codeception\InitTemplate;
use Codeception\Util\Template;
use Symfony\Component\Yaml\Yaml;

class Api extends InitTemplate
{
    protected $configTemplate = <<<EOF
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

paths:
    tests: {{baseDir}}
    output: {{baseDir}}/_output
    data: {{baseDir}}/_data
    support: {{baseDir}}/_support

settings:
    shuffle: false
    lint: true
EOF;

    protected $firstTest = <<<EOF
<?php
class ApiCest 
{    
    public function tryApi(ApiTester \$I)
    {
        \$I->sendGET('/');
        \$I->seeResponseCodeIs(200);
        \$I->seeResponseIsJson();
    }
}
EOF;


    public function setup()
    {
        $this->checkInstalled();
        $this->say("Let's prepare Codeception for REST API testing");
        $this->say("");

        $dir = $this->ask("Where tests will be stored?", 'tests');

        $url = $this->ask("Start url for tests", "http://localhost/api");

        if (!class_exists('\\Codeception\\Module\\REST') || !class_exists('\\Codeception\\Module\\PhpBrowser')) {
            $this->addModulesToComposer(['REST', 'PhpBrowser']);
        }

        $this->createEmptyDirectory($outputDir = $dir . DIRECTORY_SEPARATOR . '_output');
        $this->createEmptyDirectory($dir . DIRECTORY_SEPARATOR . '_data');
        $this->createDirectoryFor($supportDir = $dir . DIRECTORY_SEPARATOR . '_support');
        $this->createDirectoryFor($supportDir . DIRECTORY_SEPARATOR . '_generated');
        $this->gitIgnore($outputDir);
        $this->gitIgnore($supportDir . DIRECTORY_SEPARATOR . '_generated');
        $this->sayInfo("Created test directories inside at $dir");

        $configFile = (new Template($this->configTemplate))
            ->place('url', $url)
            ->place('baseDir', $dir)
            ->produce();

        if ($this->namespace) {
            $namespace = rtrim($this->namespace, '\\');
            $configFile = "namespace: $namespace\n" . $configFile;
        }

        $this->createFile('codeception.yml', $configFile);
        $this->createHelper('Api', $supportDir);
        $this->createActor('ApiTester', $supportDir, Yaml::parse($configFile)['suites']['api']);


        $this->sayInfo("Created global config codeception.yml inside the root directory");
        $this->createFile($dir . DIRECTORY_SEPARATOR . 'ApiCest.php', $this->firstTest);
        $this->sayInfo("Created a demo test ApiCest.php");


        $this->say();
        $this->saySuccess("INSTALLATION COMPLETE");

        $this->say();
        $this->say("<bold>Next steps:</bold>");
        $this->say("1. Edit <bold>$dir/ApiCest.php</bold> to write first API tests");
        $this->say("2. Run tests using: <comment>codecept run</comment>");
        $this->say();
        $this->say("<bold>Happy testing!</bold>");
    }
}
