<?php
namespace Codeception\Lib\Generator;

use Codeception\Configuration;
use Codeception\Util\Template;

class Test
{
    use Shared\Namespaces;
    use Shared\Classname;

    protected $template = <<<EOF
<?php
{{namespace}}

class {{name}}Test extends \Codeception\TestCase\Test
{
    /**
     * @var \{{actorClass}}
     */
    protected \${{actor}};

    protected function _before()
    {
    }

    protected function _after()
    {
    }

    // tests
    public function testMe()
    {

    }

}
EOF;

    protected $settings;
    protected $name;

    public function __construct($settings, $name)
    {
        $this->settings = $settings;
        $this->name = $this->removeSuffix($name, 'Test');
    }

    public function produce()
    {
        $actor = $this->settings['class_name'];
        if ($this->settings['namespace']) {
            $actor = $this->settings['namespace'] . '\\' . $actor;
        }

        $ns = $this->getNamespaceHeader($this->settings['namespace'] . '\\' . $this->name);

        return (new Template($this->template))
            ->place('namespace', $ns)
            ->place('name', $this->getShortClassName($this->name))
            ->place('actorClass', $actor)
            ->place('actor', lcfirst(Configuration::config()['actor']))
            ->produce();
    }

}
