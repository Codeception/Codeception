<?php
namespace Codeception\Lib\Generator;

use Codeception\Configuration;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
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

    protected $inheritedMethodTemplate = ' * @method void {{method}}({{params}})';

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

        return (new Template($this->template))
            ->place('hasNamespace', $namespace ? "namespace $namespace;" : '')
            ->place('actor', $this->settings['class_name'])
            ->place('inheritedMethods', $this->prependAbstractGuyDocBlocks())
            ->produce();
    }

    protected function prependAbstractGuyDocBlocks()
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
            $params = $this->getParamsString($method);
            $inherited[] = (new Template($this->inheritedMethodTemplate))
                ->place('method', $method->name)
                ->place('params', $params)
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
                $params[] = '$' . $param->name . ' = null';
            } else {
                $params[] = '$' . $param->name;
            };

        }
        return implode(', ', $params);
    }

    public function getActorName()
    {
        return $this->settings['class_name'];
    }

    public function getModules()
    {
        return array_keys($this->modules);
    }

}
