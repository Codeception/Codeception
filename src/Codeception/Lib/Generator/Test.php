<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Template;

class Test
{
    use Shared\Namespaces;
    use Shared\Classname;

    protected $template  = <<<EOF
<?php
{{namespace}}
use Codeception\Util\Stub;

class {{name}}Test extends \Codeception\TestCase\Test
{
   /**
    * @var \{{guyClass}}
    */
    protected \${{guy}};

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
        $guy = $this->settings['class_name'];
        if ($this->settings['namespace']) {
            $guy = $this->settings['namespace'].'\\'.$guy;
        }

        $ns = $this->getNamespaceString($this->settings['namespace'].'\\'.$this->name);

        return (new Template($this->template))
            ->place('namespace', $ns)
            ->place('name', $this->getShortClassName($this->name))
            ->place('guyClass', $guy)
            ->place('guy', lcfirst($guy))
            ->produce();
    }


} 