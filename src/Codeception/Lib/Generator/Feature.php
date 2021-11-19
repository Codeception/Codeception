<?php

declare(strict_types=1);

namespace Codeception\Lib\Generator;

use Codeception\Util\Template;

class Feature
{
    protected string $template = <<<EOF
Feature: {{name}}
  In order to ...
  As a ...
  I need to ...

  Scenario: try {{name}}

EOF;

    protected string $name;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function produce(): string
    {
        return (new Template($this->template))
            ->place('name', $this->name)
            ->produce();
    }
}
