<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Template;

class Cest {
    use Shared\Classname;
    use Shared\Namespaces;

    protected $template  = <<<EOF
<?php
{{namespace}}

class {{name}}Cest
{
    public function _before()
    {
    }

    public function _after()
    {
    }

    // tests
    public function tryToTest({{guy}} \$I)
    {
    }
}
EOF;

    protected $settings;
    protected $name;

    public function __construct($className, $settings)
    {
        $this->name = $this->removeSuffix($className, 'Cest');
        $this->settings = $settings;
    }

    public function produce()
    {
        $guy = $this->settings['class_name'];
        $ns = $this->getNamespaceString($this->settings['namespace'].'\\'.$this->name);
        $ns .= "use ".$this->settings['namespace'].'\\'.$guy.";";

        return (new Template($this->template))
            ->place('name', $this->getShortClassName($this->name))
            ->place('namespace', $ns)
            ->place('guy', $guy)
            ->produce();
    }

} 