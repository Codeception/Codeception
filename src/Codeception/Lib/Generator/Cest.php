<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class Cest
{
    use Shared\Classname;
    use Namespaces;

    protected $template = <<<EOF
<?php
{{namespace}}

class {{name}}Cest
{
    public function _before({{actor}} \$I)
    {
    }

    public function _after({{actor}} \$I)
    {
    }

    // tests
    public function tryToTest({{actor}} \$I)
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
        $actor = $this->settings['class_name'];
        $namespace = rtrim( $this->settings['namespace'], '\\' );
        $ns = $this->getNamespaceHeader($namespace.'\\'.$this->name);
        $ns .= "use ".$this->settings['namespace'].'\\'.$actor.";";

        return (new Template($this->template))
            ->place('name', $this->getShortClassName($this->name))
            ->place('namespace', $ns)
            ->place('actor', $actor)
            ->produce();
    }
}
