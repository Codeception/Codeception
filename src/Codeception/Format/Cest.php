<?php
namespace Codeception\Format;

use Codeception\TestCase\Interfaces\Configurable;
use Codeception\TestCase\Interfaces\ScenarioDriven;
use Codeception\TestCase\Shared\Actor;
use Codeception\TestCase\Shared\ScenarioPrint;

class Cest extends \Codeception\Test implements
    ScenarioDriven,
    Configurable
{
    use Actor;
    use ScenarioPrint;

    public function toString()
    {
        return "new test";
    }

    public function test()
    {
        $I = $this->makeIObject();
        call_user_func_array([$this->testClassInstance, $this->testMethod], [$I, $this->getScenario()]);
//
//        $this->prepareActorForTest();
//        try {
//            $this->executeHook($I, 'before');
//            $this->executeBeforeMethods($this->testMethod, $I);
//            $this->executeTestMethod($I);
//            $this->executeAfterMethods($this->testMethod, $I);
//            $this->executeHook($I, 'after');
//        } catch (\Exception $e) {
//            $this->executeHook($I, 'failed');
//            // fails and errors are now handled by Codeception\PHPUnit\Listener
//            throw $e;
//        }


    }

    public function getTestResultObject()
    {
        return $this->testResult;
    }

    public function getRawBody()
    {

    }

    public function getFeature()
    {
        return "feature";
        // TODO: Implement getFeature() method.
    }

    /**
     * @return \Codeception\Scenario
     */
    public function getScenario()
    {
        return $this->scenario;
    }

    public function getScenarioText($format = 'text')
    {
        // TODO: Implement getScenarioText() method.
    }

    public function preload()
    {
        codecept_debug('preloaded');
    }

    protected function makeIObject()
    {
        $className = '\\' . $this->actor;
        $I = new $className($this->scenario);
//        $spec = $this->getSpecFromMethod();
//
//        if ($spec) {
//            $I->wantTo($spec);
//        }
        return $I;
    }

}