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
        $this->say();
        $dir = $this->ask('Where tests will be stored?', 'tests');
        $url = $this->ask('Start URL for tests', 'http://localhost/api');

        if (!class_exists('\\Codeception\\Module\\REST') || !class_exists(PhpBrowser::class)) {
            $this->addModulesToComposer(['REST', 'PhpBrowser']);
        }

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

        $config = (new Template($this->configTemplate))
            ->place('url', $url)
            ->place('baseDir', $dir)
            ->produce();

        $namespace = rtrim($this->namespace, '\\');
        $config = "namespace: $namespace\nsupport_namespace: {$this->supportNamespace}\n" . $config;
        $this->createFile('codeception.yml', $config);

        $settings = Yaml::parse($config)['suites']['api'];
        $settings['support_namespace'] = $this->supportNamespace;
        $this->createActor('ApiTester', $dir . '/Support', $settings);

        $this->sayInfo('Created global config codeception.yml inside the root directory');

        $firstTest = (new Template($this->firstTest))
            ->place('namespace', $namespace)
            ->place('support_namespace', $this->supportNamespace)
            ->produce();
        $this->createFile($dir . '/ApiCest.php', $firstTest);
        $this->sayInfo('Created a demo test ApiCest.php');

        $this->say();
        $this->saySuccess('INSTALLATION COMPLETE');
        $this->say();
        $this->say('<bold>Next steps:</bold>');
        $this->say("1. Edit <bold>{$dir}/ApiCest.php</bold> to write first API tests");
        $this->say("2. Run tests using: <comment>codecept run</comment>");
        $this->say();
        $this->say('<bold>Happy testing!</bold>');
    }
}
