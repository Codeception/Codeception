<?php

class DummyClass
{
    /**
     * @var int|string
     */
    protected $checkMe = 1;

    protected array $properties = ['checkMeToo' => 1];

    public function __construct($checkMe = 1)
    {
        $this->checkMe = "constructed: " . $checkMe;
    }

    public function helloWorld(): string
    {
        return "hello";
    }

    public function goodByeWorld(): string
    {
        return "good bye";
    }

    protected function notYourBusinessWorld(): string
    {
        return "goAway";
    }

    public function getCheckMe(): string
    {
        return $this->checkMe;
    }

    public function getCheckMeToo()
    {
        return $this->checkMeToo;
    }

    public function call(): bool
    {
        $this->targetMethod();
        return true;
    }

    public function targetMethod(): bool
    {
        return true;
    }

    public function exceptionalMethod(): void
    {
        throw new Exception('Catch it!');
    }

    public function __set($name, $value)
    {
        if ($this->isMagical($name)) {
            $this->properties[$name] = $value;
        }
    }

    public function __get($name)
    {
        if ($this->__isset($name)) {
            return $this->properties[$name];
        }
    }

    public function __isset($name)
    {
        return $this->isMagical($name) && isset($this->properties[$name]);
    }

    private function isMagical($name): bool
    {
        $reflectionClass = new \ReflectionClass($this);
        return !$reflectionClass->hasProperty($name);
    }
}
