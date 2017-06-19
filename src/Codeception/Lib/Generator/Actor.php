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
                $params[] = '$' . $param->name . ' = '.$this->getDefaultValue($param);
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

    /**
     * Infer default parameter from the reflection object and format it as PHP (code) string
     *
     * @param \ReflectionParameter $param
     *
     * @return string
     */
    private function getDefaultValue(\ReflectionParameter $param)
    {
        if ($param->isDefaultValueAvailable()) {
            if (method_exists($param, 'isDefaultValueConstant') && $param->isDefaultValueConstant()) {
                $constName = $param->getDefaultValueConstantName();
                if (false !== strpos($constName, '::')) {
                    list($class, $const) = explode('::', $constName);
                    if (in_array($class, ['self', 'static'])) {
                        $constName = $param->getDeclaringClass()->getName().'::'.$const;
                    }
                }

                return $constName;
            }

            return $this->phpEncodeValue($param->getDefaultValue());
        }

        return 'null';
    }

    /**
     * PHP encoded a value
     *
     * @param mixed $value
     *
     * @return string
     */
    private function phpEncodeValue($value)
    {
        if (is_array($value)) {
            return $this->phpEncodeArray($value);
        }

        if (is_string($value)) {
            return json_encode($value);
        }

        return var_export($value, true);
    }

    /**
     * Recursively PHP encode an array
     *
     * @param array $array
     *
     * @return string
     */
    private function phpEncodeArray(array $array)
    {
        $isPlainArray = function (array $value) {
            return ((count($value) === 0)
                || (
                    (array_keys($value) === range(0, count($value) - 1))
                    && (0 === count(array_filter(array_keys($value), 'is_string'))))
            );
        };

        if ($isPlainArray($array)) {
            return '['.implode(', ', array_map([$this, 'phpEncodeValue'], $array)).']';
        }

        return '['.implode(', ', array_map(function ($key) use ($array) {
            return $this->phpEncodeValue($key).' => '.$this->phpEncodeValue($array[$key]);
        }, array_keys($array))).']';
    }
}
