<?php
namespace Helper;

// here you can define custom actions
// all public methods declared in helper class will be available in $I

class Unit extends \Codeception\Module
{
    public function seeSomething(): void
    {

    }

    public function getInt(): int
    {
        throw new \RuntimeException(__METHOD__ . ' should not be executed');
    }

    public function getDomDocument(): \DOMDocument
    {
        throw new \RuntimeException(__METHOD__ . ' should not be executed');
    }

    public function getUnion(): int|\DOMDocument
    {
        throw new \RuntimeException(__METHOD__ . ' should not be executed');
    }

    public function getIntersection(): \Iterator&\Countable&\DOMDocument
    {
        throw new \RuntimeException(__METHOD__ . ' should not be executed');
    }

    public function getSelf(): self
    {
        throw new \RuntimeException(__METHOD__ . ' should not be executed');
    }

    public function getParent(): parent
    {
        throw new \RuntimeException(__METHOD__ . ' should not be executed');
    }
}
