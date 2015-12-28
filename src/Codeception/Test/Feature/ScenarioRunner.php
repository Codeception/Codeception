<?php
namespace Codeception\Test\Feature;

use Codeception\Event\StepEvent;
use Codeception\Events;
use Codeception\Exception\ConditionalAssertionFailed;
use Codeception\Lib\ModuleContainer;
use Codeception\Scenario;
use Codeception\Step;
use Codeception\Test\Metadata;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventDispatcher;

trait ScenarioRunner
{

    /**
     * @return EventDispatcher
     */
    abstract function getDispatcher();

    /**
     * @return ModuleContainer
     */
    abstract function getModuleContainer();

    /**
     * @return Metadata
     */
    abstract function getMetadata();


    /**
      * @return \PHPUnit_Framework_TestResult
      */
     abstract public function getTestResultObject();

    /**
     * @var Scenario
     */
    protected $scenario;

    protected function createScenario()
    {
        $this->scenario = new Scenario($this);
    }

    /**
     * @return Scenario
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    public function getFeature()
    {
        return $this->getScenario()->getFeature();
    }
}