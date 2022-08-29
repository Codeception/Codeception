<?php

declare(strict_types=1);

namespace Codeception\Lib\Generator;

use Codeception\Util\Template;

class Feature
{
    /**
     * @var string
     */
    protected $template = <<<EOF
Feature: {{name}}
  In order to ...
  As a ...
  I need to ...

  Scenario: try {{name}}

EOF;

    /**
     * @var string
     */
    protected $name;

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
