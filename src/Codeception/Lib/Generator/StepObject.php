<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Template;

class StepObject {
    use Shared\Namespaces;

    protected $template = <<<EOF
<?php
{{namespace}}
class {{name}}Steps extends {{guyClass}}
{
{{actions}}
}
EOF;

    protected $actionTemplate = <<<EOF
    function {{action}}()
    {
        \$I = \$this;
    }
EOF;

    protected $settings;
    protected $name;
    protected $actions = "";

    public function __construct($settings, $name)
    {
        $this->settings = $settings;
        $this->name = $name;
    }

    public function produce()
    {
        $guy = $this->settings['class_name'];        
        $ns = $this->getNamespaceString($this->settings['namespace'].'\\'.$guy . '\\' .$this->name);
        $ns = ltrim($ns, '\\');

        $extended = '\\'.ltrim('\\'.$this->settings['namespace'].'\\'.$guy, '\\');

        return (new Template($this->template))
            ->place('namespace', $ns)
            ->place('name', $this->name)
            ->place('guyClass', $extended)
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