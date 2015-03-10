<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Template;

class StepObject
{
    use Shared\Namespaces;
    use Shared\Classname;

    protected $template = <<<EOF
<?php
namespace {{namespace}};

class {{name}} extends {{actorClass}}
{
{{actions}}
}
EOF;

    protected $actionTemplate = <<<EOF

    public function {{action}}()
    {
        \$I = \$this;
    }

EOF;

    protected $settings;
    protected $name;
    protected $actions = '';

    public function __construct($settings, $name)
    {
        $this->settings = $settings;
        $this->name = $this->getShortClassName($name);
        $this->namespace = $this->getNamespaceString($this->settings['namespace'] . '\\Step\\' . $name);
    }

    public function produce()
    {
        $actor = $this->settings['class_name'];
        $extended = '\\' . ltrim('\\' . $this->settings['namespace'] . '\\' . $actor, '\\');

        return (new Template($this->template))
            ->place('namespace', $this->namespace)
            ->place('name', $this->name)
            ->place('actorClass', $extended)
            ->place('actions', $this->actions)
            ->produce();
    }

    public function createAction($action)
    {
        $this->actions .= (new Template($this->actionTemplate))
            ->place('action', $action)
            ->produce();
    }

}
