<?php
namespace Codeception\Lib\Generator;

use Codeception\Configuration;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\Util\ReflectionHelper;
use Codeception\Util\Template;

class Actor
{
    protected $template = <<<EOF
<?php
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

    protected $inheritedMethodTemplate = ' * @method {{return}} {{method}}({{params}})';

    protected $settings;
    protected $modules;
    protected $actions;

    public function __construct($settings)
    {
        $this->settings = $settings;
        $this->di = new Di();
        $this->moduleContainer = new ModuleContainer($this->di, $settings);

        $modules = Configuration::modules($this->settings);
        foreach ($modules as $moduleName) {
            $this->moduleContainer->create($moduleName);
        }

        $this->modules = $this->moduleContainer->all();
        $this->actions = $this->moduleContainer->getActions();
    }

    public function produce()
    {
        $namespace = rtrim($this->settings['namespace'], '\\');

        if (!isset($this->settings['actor']) && isset($this->settings['class_name'])) {
            $this->settings['actor'] = $this->settings['class_name'];
        }

        return (new Template($this->template))
            ->place('hasNamespace', $namespace ? "namespace $namespace;" : '')
            ->place('actor', $this->settings['actor'])
            ->place('inheritedMethods', $this->prependAbstractActorDocBlocks())
            ->produce();
    }

    protected function prependAbstractActorDocBlocks()
    {
        $inherited = [];

        $class = new \ReflectionClass('\Codeception\\Actor');
        $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if ($method->name == '__call') {
                continue;
            } // skipping magic
            if ($method->name == '__construct') {
                continue;
            } // skipping magic
            $returnType = 'void';
            if ($method->name == 'haveFriend') {
                $returnType = '\Codeception\Lib\Friend';
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

    /**
     * @param \ReflectionMethod $refMethod
     * @return array
     */
    protected function getParamsString(\ReflectionMethod $refMethod)
    {
        $params = [];
        foreach ($refMethod->getParameters() as $param) {
            if ($param->isOptional()) {
                $params[] = '$' . $param->name . ' = ' . ReflectionHelper::getDefaultValue($param);
            } else {
                $params[] = '$' . $param->name;
            };
        }
        return implode(', ', $params);
    }

    public function getActorName()
    {
        return $this->settings['actor'];
    }

    public function getModules()
    {
        return array_keys($this->modules);
    }
}
