<?php
namespace Codeception\Lib\Generator;

use Codeception\Util\Template;

class Feature
{
    protected $template = <<<EOF
Feature: {{name}}
  In order to ...
  As a ...
  I need to ...

  Scenario: try {{name}}

EOF;

    protected $name;

    public function __construct($name)
    {
        $this->name = $name;
    }

    public function produce()
    {
        return (new Template($this->template))
            ->place('name', $this->name)
            ->produce();
    }
}
