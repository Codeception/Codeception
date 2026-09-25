<?php

declare(strict_types=1);

namespace Codeception\Template;

use Codeception\InitTemplate;
use Codeception\Template\Shared\TemplateHelpersTrait;
use Codeception\Util\Template;
use Symfony\Component\Yaml\Yaml;

class Api extends InitTemplate
{
    use TemplateHelpersTrait;

    protected string $configTemplate = <<<EOF
# suite config
suites:
    'Api:
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
        $this->createSuiteDirs($dir);
        $this->sayInfo("Created test directories at {$dir}");
        $this->ensureModules(['REST', 'PhpBrowser']);
        $namespace = rtrim($this->namespace, '\\');

        if ($this->isPhp()) {
            $this->createFile('codeception.php', $this->phpConfig($namespace, $dir, $url));
            $settings = $this->loadPhpSuiteSettings('Api');
            $this->sayInfo('Created global config codeception.php inside the root directory');
        } else {
            $config = (new Template($this->configTemplate))
                ->place('url', $url)
                ->place('baseDir', $dir)
                ->produce();
            $config = "namespace: $namespace\nsupport_namespace: {$this->supportNamespace}\n" . $config;
            $this->createFile('codeception.yml', $config);
            $settings = Yaml::parse($config)['suites']['Api'];
            $this->sayInfo('Created global config codeception.yml inside the root directory');
        }

        $settings['support_namespace'] = $this->supportNamespace;
        $this->createActor('ApiTester', $dir . DIRECTORY_SEPARATOR . 'Support', $settings);

        $firstTest = (new Template($this->firstTest))
            ->place('namespace', $namespace)
            ->place('support_namespace', $this->supportNamespace)
            ->produce();
        $this->createFile($dir . DIRECTORY_SEPARATOR . 'ApiCest.php', $firstTest);
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

    private function phpConfig(string $namespace, string $dir, string $url): string
    {
        $namespace = $this->phpLiteral($namespace);
        $support   = $this->phpLiteral($this->supportNamespace);
        $dir       = $this->phpLiteral($dir);
        $url       = $this->phpLiteral($url);

        return <<<EOF
<?php

declare(strict_types=1);

use Codeception\\Config\\GlobalConfig;
use Codeception\\Config\\SuiteConfig;

return GlobalConfig::create()
    ->namespace('{$namespace}')
    ->supportNamespace('{$support}')
    ->paths(
        tests: '{$dir}',
        output: '{$dir}/_output',
        data: '{$dir}/Support/Data',
        support: '{$dir}/Support',
    )
    ->settings(shuffle: false, lint: true)
    ->suite('Api', SuiteConfig::create()
        ->actor('ApiTester')
        ->path('.')
        ->module('REST', ['url' => '{$url}'], depends: 'PhpBrowser')
        ->stepDecorators(['Codeception\\Step\\AsJson']));

EOF;
    }
}
