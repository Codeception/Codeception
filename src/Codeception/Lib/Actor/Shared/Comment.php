<?php

declare(strict_types=1);

namespace Codeception\Lib\Actor\Shared;

use Codeception\Scenario;

trait Comment
{
    abstract protected function getScenario(): Scenario;

    public function expectTo(string $prediction): self
    {
        return $this->comment('I expect to ' . $prediction);
    }

    public function expect(string $prediction): self
    {
        return $this->comment('I expect ' . $prediction);
    }

    public function amGoingTo(string $argumentation): self
    {
        return $this->comment('I am going to ' . $argumentation);
    }

    public function am(string $role): self
    {
        $role = trim($role);

        if (stripos('aeiou', (string)$role[0]) !== false) {
            return $this->comment('As an ' . $role);
        }

        return $this->comment('As a ' . $role);
    }

    public function lookForwardTo(string $achieveValue): self
    {
        return $this->comment('So that I ' . $achieveValue);
    }

    public function comment(string $description): self
    {
        $this->getScenario()->comment($description);
        return $this;
    }
}
