<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Template;

class Cest {
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
    protected $className;

    public function __construct($className, $settings)
    {
        $this->className = $className;
        $this->settings = $settings;
    }

    public function produce()
    {
        $guy = $this->settings['class_name'];
        $ns = $this->getNamespaceString($this->settings['namespace'].'\\'.$this->className);
        $ns .= "use ".$this->settings['namespace'].'\\'.$guy.";";

        return (new Template($this->template))
            ->place('name', $this->className)
            ->place('namespace', $ns)
            ->place('guy', $guy)
            ->produce();
    }

} 