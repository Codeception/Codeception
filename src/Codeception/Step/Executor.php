<?php
namespace Codeception\Step;
 
class Executor extends \Codeception\Step {
    const LAZY_EXECUTOR = 'lazy-executor';

    protected $callable = null;

    public function __construct(\Closure $callable, $arguments = array())
    {
        parent::__construct(self::LAZY_EXECUTOR, array());

        $this->callable = $callable;
    }

    public function run()
    {
        $callable = $this->callable;

        return $callable();
    }

}
