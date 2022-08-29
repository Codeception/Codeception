<?php

declare(strict_types=1);

namespace Codeception\Lib\Generator;

use Codeception\Lib\Generator\Shared\Classname;
use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class PageObject
{
    use Namespaces;
    use Classname;

    protected string $template = <<<EOF
<?php

declare(strict_types=1);

namespace {{namespace}};

class {{class}}
{
    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public \$usernameField = '#username';
     * public \$formSubmitButton = "#mainForm input[type=submit]";
     */

{{actions}}
}

EOF;

    protected string $actionsTemplate = <<<EOF
    /**
     * @var \\{{actorClass}};
     */
    protected \${{actor}};

    public function __construct(\\{{actorClass}} \$I)
    {
        \$this->{{actor}} = \$I;
        // you can inject other page objects here as well
    }

EOF;

    protected string $actions = '';

    protected string $name;

    protected string $namespace;

    public function __construct(protected array $settings, string $name)
    {
        $this->name = $this->getShortClassName($name);
        $this->namespace = $this->getNamespaceString($this->supportNamespace() . '\\Page\\' . $name);
    }

    public function produce(): string
    {
        return (new Template($this->template))
            ->place('namespace', $this->namespace)
            ->place('actions', $this->produceActions())
            ->place('class', $this->name)
            ->produce();
    }

    protected function produceActions(): string
    {
        if (!isset($this->settings['actor'])) {
            return ''; // global pageobject
        }

        $actor = lcfirst($this->settings['actor']);
        $actorClass = ltrim($this->supportNamespace() . $this->settings['actor'], '\\');

        return (new Template($this->actionsTemplate))
            ->place('actorClass', $actorClass)
            ->place('actor', $actor)
            ->place('pageObject', $this->name)
            ->produce();
    }
}
