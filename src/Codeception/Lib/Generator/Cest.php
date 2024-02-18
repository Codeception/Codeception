<?php

declare(strict_types=1);

namespace Codeception\Lib\Generator;

use Codeception\Exception\ConfigurationException;
use Codeception\Lib\Generator\Shared\Classname;
use Codeception\Util\Shared\Namespaces;
use Codeception\Util\Template;

class Cest
{
    use Classname;
    use Namespaces;

    protected string $template = <<<EOF
<?php
declare(strict_types=1);
{{namespace}}

final class {{name}}Cest
{
    public function _before({{actor}} \$I): void
    {
        // Code here will be executed before each test.
        // To execute code before just *some* tests, use the `#[Before]` attribute, see https://codeception.com/docs/AdvancedUsage#BeforeAfter-Attributes
    }

    // Your tests. All `public` methods will be executed as tests:

    public function tryToTest({{actor}} \$I, Scenario \$scenario): void
    {
    }
}

EOF;

    protected ?string $name;

    public function __construct(string $className, protected array $settings)
    {
        $this->name = $this->removeSuffix($className, 'Cest');
    }

    public function produce(): string
    {
        $actor = $this->settings['actor'];
        if (!$actor) {
            throw new ConfigurationException("Cest can't be created for suite without an actor. Add `actor: SomeTester` to suite config");
        }

        $namespaceHeader = $this->getNamespaceHeader($this->settings['namespace'] . '\\' . ucfirst((string) $this->settings['suite']) . '\\' . $this->name);

        if ($namespaceHeader) {
            $namespaceHeader .= "\nuse " . $this->supportNamespace() . $actor . ";";
            $namespaceHeader .= "\nuse Codeception\\Scenario;";
        }

        return (new Template($this->template))
            ->place('name', $this->getShortClassName($this->name))
            ->place('namespace', $namespaceHeader)
            ->place('actor', $actor)
            ->produce();
    }
}
