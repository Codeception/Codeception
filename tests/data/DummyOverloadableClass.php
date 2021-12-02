<?php

class DummyOverloadableClass
{
    /**
     * @var int|string
     */
    protected $checkMe = 1;

    protected array $properties = array('checkMeToo' => 1);

    function __construct($checkMe = 1)
    {
        $this->checkMe = "constructed: ".$checkMe;
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

    public function getCheckMe(): string {
        return $this->checkMe;
    }

    public function getCheckMeToo(): ?int
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

    public function exceptionalMethod(): void {
        throw new Exception('Catch it!');
    }

    public function __get($name) {
        //seeing as we're not implementing __set here, add check for __mocked
        $return = null;
        if ($name === '__mocked') {
            $return = $this->__mocked ?? null;
        } elseif ($this->__isset($name)) {
            $return = $this->properties[$name];
        }

        return $return;
    }

    public function __isset($name) {
        return $this->isMagical($name) && isset($this->properties[$name]);
    }

    private function isMagical($name): bool
    {
        $reflectionClass = new \ReflectionClass($this);
        return !$reflectionClass->hasProperty($name);
    }
}
