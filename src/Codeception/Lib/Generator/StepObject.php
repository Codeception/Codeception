<?php

declare(strict_types=1);

namespace Codeception\Lib\Generator;

use Codeception\Exception\ConfigurationException;
use Codeception\Lib\Generator\Shared\Classname;
use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class StepObject
{
    use Namespaces;
    use Classname;

    protected string $template = <<<EOF
<?php

declare(strict_types=1);

namespace {{namespace}};

class {{name}} extends {{actorClass}}
{
{{actions}}
}
EOF;

    protected string $actionTemplate = <<<EOF

    public function {{action}}()
    {
        \$I = \$this;
    }

EOF;

    protected array $settings = [];

    protected string $name;

    protected string $actions = '';

    public string $namespace;

    public function __construct(array $settings, string $name)
    {
        $this->settings = $settings;
        $this->name = $this->getShortClassName($name);
        $this->namespace = $this->getNamespaceString($this->settings['namespace'] . '\\Step\\' . $name);
    }

    public function produce(): string
    {
        $actor = $this->settings['actor'];
        if (!$actor) {
            throw new ConfigurationException("Steps can't be created for suite without an actor");
        }
        $ns = $this->getNamespaceString($this->settings['namespace'] . '\\' . $actor . '\\' . $this->name);
        $ns = ltrim($ns, '\\');

        $extended = '\\' . ltrim('\\' . $this->settings['namespace'] . '\\' . $actor, '\\');

        return (new Template($this->template))
            ->place('namespace', $this->namespace)
            ->place('name', $this->name)
            ->place('actorClass', $extended)
            ->place('actions', $this->actions)
            ->produce();
    }

    public function createAction($action): void
    {
        $this->actions .= (new Template($this->actionTemplate))
            ->place('action', $action)
            ->produce();
    }
}
