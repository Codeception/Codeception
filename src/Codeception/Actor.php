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

    /**
     * This method is used by Cept format to add description to test output
     *
     * It can be used by Cest format too.
     * It doesn't do anything when called, but it is parsed by Parser before execution
     *
     * @see \Codeception\Lib\Parser::parseFeature
     */
    public function wantTo(string $text): void
    {
    }

    public function wantToTest(string $text): void
    {
    }

    public function __call(string $method, array $arguments)
    {
        $class = static::class;
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
