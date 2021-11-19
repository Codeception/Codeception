<?php

declare(strict_types=1);

namespace Codeception\Lib\Generator;

use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class Snapshot
{
    use Namespaces;

    protected string $template = <<<EOF
<?php

declare(strict_types=1);

namespace {{namespace}};

class {{name}} extends \\Codeception\\Snapshot
{

{{actions}}

    protected function fetchData()
    {
        // TODO: return a value which will be used for snapshot 
    }
}
EOF;

    protected string $actionsTemplate = <<<EOF
    /**
     * @var \\{{actorClass}};
     */
    protected \${{actor}};

    public function __construct(\\{{actorClass}} \$I)
    {
        \$this->{{actor}} = \$I;
    }
EOF;

    protected string $namespace;

    protected string $name;

    protected array $settings = [];

    public function __construct(array $settings, string $name)
    {
        $this->settings = $settings;
        $this->name = $this->getShortClassName($name);
        $this->namespace = $this->getNamespaceString($this->settings['namespace'] . '\\Snapshot\\' . $name);
    }

    public function produce(): string
    {
        return (new Template($this->template))
            ->place('namespace', $this->namespace)
            ->place('actions', $this->produceActions())
            ->place('name', $this->name)
            ->produce();
    }

    protected function produceActions(): string
    {
        if (!isset($this->settings['actor'])) {
            return ''; // no actor in suite
        }

        $actor = lcfirst($this->settings['actor']);
        $actorClass = $this->settings['actor'];
        if (!empty($this->settings['namespace'])) {
            $actorClass = rtrim($this->settings['namespace'], '\\') . '\\' . $actorClass;
        }

        return (new Template($this->actionsTemplate))
            ->place('actorClass', $actorClass)
            ->place('actor', $actor)
            ->produce();
    }
}
