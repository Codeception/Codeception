<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Template;

class Group
{

    use Shared\Namespaces;
    use Shared\Classname;

    protected $template = <<<EOF
<?php
namespace {{namespace}};

use \Codeception\Event\TestEvent;
/**
 * Group class is Codeception Extension which is allowed to handle to all internal events.
 * This class itself can be used to listen events for test execution of one particular group.
 * It may be especially useful to create fixtures data, prepare server, etc.
 *
 * INSTALLATION:
 *
 * To use this group extension, include it to "extensions" option of global Codeception config.
 */

class {{class}} extends \Codeception\Platform\Group
{
    public static \$group = '{{groupName}}';

    public function _before(TestEvent \$e)
    {
    }

    public function _after(TestEvent \$e)
    {
    }
}
EOF;

    protected $name;
    protected $namespace;
    protected $settings;

    public function __construct($settings, $name)
    {
        $this->settings = $settings;
        $this->name = $name;
        $this->namespace = $this->getNamespaceString($this->settings['namespace'] . '\\Group\\' . $name);

    }

    public function produce()
    {
        return (new Template($this->template))
            ->place('class', ucfirst($this->name))
            ->place('name', $this->name)
            ->place('namespace', $this->namespace)
            ->place('groupName', strtolower($this->name))
            ->produce();
    }

}
