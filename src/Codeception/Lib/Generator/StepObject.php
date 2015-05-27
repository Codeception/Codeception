<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class StepObject
{
    use Namespaces;
    use Shared\Classname;

    protected $template = <<<EOF
<?php
{{namespace}}
class {{name}}Steps extends {{actorClass}}
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
        $this->name = $this->removeSuffix($name, 'Steps');
    }

    public function produce()
    {
        $actor = $this->settings['class_name'];
        $ns = $this->getNamespaceString($this->settings['namespace'] . '\\' . $actor . '\\' . $this->name);
        $ns = ltrim($ns, '\\');

        $extended = '\\' . ltrim('\\' . $this->settings['namespace'] . '\\' . $actor, '\\');

        return (new Template($this->template))
            ->place('namespace', $ns)
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
