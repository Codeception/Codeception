<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Template;

class Helper
{

    protected $template = <<<EOF
<?php
namespace {{namespace}}Codeception\Module;

// here you can define custom actions
// all public methods declared in helper class will be available in \$I

class {{name}}Helper extends \\Codeception\\Module
{

}

EOF;

    protected $namespace;
    protected $name;

    public function __construct($name, $namespace = '')
    {
        $this->namespace = $namespace
            ? $namespace . '\\'
            : '';
        $this->name = $name;
    }

    public function produce()
    {
        return (new Template($this->template))
            ->place('namespace', $this->namespace)
            ->place('name', $this->name)
            ->produce();
    }

}