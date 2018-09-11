<?php
namespace Codeception\Lib\Generator;

use Codeception\Exception\ConfigurationException;
use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class Cest
{
    use Shared\Classname;
    use Namespaces;

    protected $template = <<<EOF
<?php {{namespace}}

class {{name}}Cest
{
    public function _before({{actor}} \$I)
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
        $actor = $this->settings['actor'];
        if (!$actor) {
            throw new ConfigurationException("Cept can't be created for suite without an actor. Add `actor: SomeTester` to suite config");
        }

        if (array_key_exists('suite_namespace', $this->settings)) {
            $namespace = rtrim($this->settings['suite_namespace'], '\\');
        } else {
            $namespace = rtrim($this->settings['namespace'], '\\');
        }

        $ns = $this->getNamespaceHeader($namespace.'\\'.$this->name);

        if ($namespace) {
            $ns .= "use ".$this->settings['namespace'].'\\'.$actor.";";
        }

        return (new Template($this->template))
            ->place('name', $this->getShortClassName($this->name))
            ->place('namespace', $ns)
            ->place('actor', $actor)
            ->produce();
    }
}
