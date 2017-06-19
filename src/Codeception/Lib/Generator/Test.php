<?php
namespace Codeception\Lib\Generator;

use Codeception\Configuration;
use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class Test
{
    use Namespaces;
    use Shared\Classname;

    protected $template = <<<EOF
<?php
{{namespace}}

class {{name}}Test extends \Codeception\Test\Unit
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
    public function testSomeFeature()
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
        $actor = $this->settings['actor'];
        if ($this->settings['namespace']) {
            $actor = $this->settings['namespace'] . '\\' . $actor;
        }

        $ns = $this->getNamespaceHeader($this->settings['namespace'] . '\\' . $this->name);

        return (new Template($this->template))
            ->place('namespace', $ns)
            ->place('name', $this->getShortClassName($this->name))
            ->place('actorClass', $actor)
            ->place('actor', lcfirst(Configuration::config()['actor_suffix']))
            ->produce();
    }
}
