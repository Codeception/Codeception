<?php

class DummyClass
{
    /**
     * @var int|string
     */
    protected $checkMe = 1;

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

    public function getCheckMe()
    {
        return $this->checkMe;
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

    public function exceptionalMethod()
    {
        throw new Exception('Catch it!');
    }
}
