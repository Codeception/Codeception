<?php

declare(strict_types=1);

namespace Codeception\Lib\Generator;

use Codeception\Configuration;
use Codeception\Lib\Di;
use Codeception\Lib\Friend;
use Codeception\Lib\Generator\Shared\Classname;
use Codeception\Lib\ModuleContainer;
use Codeception\Util\ReflectionHelper;
use Codeception\Util\Template;
use ReflectionClass;
use ReflectionMethod;

class Actor
{
    use Classname;

    public Di $di;

    public ModuleContainer $moduleContainer;

    protected string $template = <<<EOF
<?php

declare(strict_types=1);
{{hasNamespace}}

/**
 * Inherited Methods
{{inheritedMethods}}
 *
 * @SuppressWarnings(PHPMD)
*/
class {{actor}} extends \Codeception\Actor
{
    use _generated\{{actor}}Actions;

    /**
     * Define custom actions here
     */
}

EOF;

    protected string $inheritedMethodTemplate = ' * @method {{return}} {{method}}({{params}})';

    protected array $modules = [];

    protected array $actions = [];

    public function __construct(protected array $settings)
    {
        $this->di = new Di();
        $this->moduleContainer = new ModuleContainer($this->di, $settings);

        $modules = Configuration::modules($this->settings);
        foreach ($modules as $moduleName) {
            $this->moduleContainer->create($moduleName);
        }

        $this->modules = $this->moduleContainer->all();
        $this->actions = $this->moduleContainer->getActions();
    }

    public function produce(): string
    {
        $namespace = trim($this->supportNamespace(), '\\');

        return (new Template($this->template))
            ->place('hasNamespace', $namespace !== '' ? "\nnamespace {$namespace};" : '')
            ->place('actor', $this->settings['actor'])
            ->place('inheritedMethods', $this->prependAbstractActorDocBlocks())
            ->produce();
    }

    protected function prependAbstractActorDocBlocks(): string
    {
        $inherited = [];

        $class = new ReflectionClass(\Codeception\Actor::class);
        $methods = $class->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if ($method->name == '__call') {
                continue;
            } // skipping magic
            if ($method->name == '__construct') {
                continue;
            } // skipping magic
            $returnType = 'void';
            if ($method->name == 'haveFriend') {
                $returnType = Friend::class;
            }
            $params = $this->getParamsString($method);
            $inherited[] = (new Template($this->inheritedMethodTemplate))
                ->place('method', $method->name)
                ->place('params', $params)
                ->place('return', $returnType)
                ->produce();
        }

        return implode("\n", $inherited);
    }

    protected function getParamsString(ReflectionMethod $refMethod): string
    {
        $params = [];
        foreach ($refMethod->getParameters() as $param) {
            if ($param->isOptional()) {
                $params[] = '$' . $param->name . ' = ' . ReflectionHelper::getDefaultValue($param);
            } else {
                $params[] = '$' . $param->name;
            }
        }
        return implode(', ', $params);
    }

    public function getActorName()
    {
        return $this->settings['actor'];
    }

    /**
     * @return string[]
     */
    public function getModules(): array
    {
        return array_keys($this->modules);
    }
}
