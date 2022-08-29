<?php

declare(strict_types=1);

namespace Codeception\Lib\Generator;

use Codeception\Configuration;
use Codeception\Lib\Generator\Shared\Classname;
use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class Test
{
    use Namespaces;
    use Classname;

    protected string $template = <<<EOF
<?php

{{namespace}}

class {{name}}Test extends \Codeception\Test\Unit
{
{{tester}}
    protected function _before()
    {
    }

    // tests
    public function testSomeFeature()
    {

    }
}

EOF;

    protected string $testerTemplate = <<<EOF

    protected {{actorClass}} \${{actor}};

EOF;

    protected string $name;

    public function __construct(protected array $settings, string $name)
    {
        $this->name = $this->removeSuffix($name, 'Test');
    }

    public function produce(): string
    {
        $actor = $this->settings['actor'];

        $ns = $this->getNamespaceHeader($this->settings['namespace'] . '\\' . ucfirst($this->settings['suite']) . '\\' . $this->name);

        if ($ns) {
            $ns .= "\nuse " . $this->supportNamespace() . $actor . ";";
        }


        $tester = '';
        if ($this->settings['actor']) {
            $tester = (new Template($this->testerTemplate))
                ->place('actorClass', $actor)
                ->place('actor', lcfirst(Configuration::config()['actor_suffix']))
                ->produce();
        }

        return (new Template($this->template))
            ->place('namespace', $ns)
            ->place('name', $this->getShortClassName($this->name))
            ->place('tester', $tester)
            ->produce();
    }
}
