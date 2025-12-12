<?php

declare(strict_types=1);

namespace Codeception\Lib\Generator;

use Codeception\Lib\Generator\Shared\Classname;
use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class Group
{
    use Namespaces;
    use Classname;

    protected string $template = <<<EOF
<?php

declare(strict_types=1);

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

    protected string $namespace;

    public function __construct(protected array $settings, protected string $name)
    {
        $this->namespace = $this->getNamespaceString($this->supportNamespace() . '\\Group\\' . $name);
    }

    public function produce(): string
    {
        return (new Template($this->template))
            ->place('class', ucfirst($this->name))
            ->place('name', $this->name)
            ->place('namespace', $this->namespace)
            ->place('groupName', strtolower($this->name))
            ->produce();
    }
}
