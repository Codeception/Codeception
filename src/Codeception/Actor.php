<?php

declare(strict_types=1);

namespace Codeception;

use Closure;
use Codeception\Lib\Actor\Shared\Comment;
use Codeception\Lib\Actor\Shared\Pause;
use Codeception\Step\Executor;
use RuntimeException;

abstract class Actor
{
    use Comment;
    use Pause;

    public function __construct(protected Scenario $scenario)
    {
    }

    protected function getScenario(): Scenario
    {
        return $this->scenario;
    }

    public function wantToTest(string $text): void
    {
        $this->wantTo('test ' . $text);
    }

    public function wantTo(string $text): void
    {
        $this->scenario->setFeature($text);
    }

    public function __call(string $method, array $arguments)
    {
        $class = $this::class;
        throw new RuntimeException("Call to undefined method {$class}::{$method}");
    }

    /**
     * Lazy-execution given anonymous function
     */
    public function execute(Closure $callable): self
    {
        $this->scenario->addStep(new Executor($callable, []));
        $callable();
        return $this;
    }
}
