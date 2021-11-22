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
    // include url of current page
    public static \$URL = '';

    /**
     * Declare UI map for this page here. CSS or XPath allowed.
     * public static \$usernameField = '#username';
     * public static \$formSubmitButton = "#mainForm input[type=submit]";
     */

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: Page\\Edit::route('/123-post');
     */
    public static function route(\$param)
    {
        return static::\$URL.\$param;
    }

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
    }

EOF;

    protected string $actions = '';

    protected array $settings = [];

    protected string $name;

    protected string $namespace;

    public function __construct(array $settings, string $name)
    {
        $this->settings = $settings;
        $this->name = $this->getShortClassName($name);
        $this->namespace = $this->getNamespaceString($this->settings['namespace'] . '\\Page\\' . $name);
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
        $actorClass = $this->settings['actor'];
        if (!empty($this->settings['namespace'])) {
            $actorClass = rtrim($this->settings['namespace'], '\\') . '\\' . $actorClass;
        }

        return (new Template($this->actionsTemplate))
            ->place('actorClass', $actorClass)
            ->place('actor', $actor)
            ->place('pageObject', $this->name)
            ->produce();
    }
}
