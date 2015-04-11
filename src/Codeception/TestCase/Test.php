<?php

namespace Codeception\TestCase;

use Codeception\Configuration;
use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\TestCase;
use Codeception\Util\Annotation;

class Test extends TestCase implements
    Interfaces\Descriptive,
    Interfaces\Configurable
{
    use Shared\Actor;
    use Shared\Dependencies;

    protected function setUp()
    {
        $actor = $this->actor;
        if ($actor) {
            $property = lcfirst(Configuration::config()['actor']);
            $this->$property = new $actor($this->scenario);

            // BC compatibility hook
            $actorProperty = lcfirst($actor);
            $this->$actorProperty = $this->$property;
        }
        $this->getScenario()->stopIfBlocked();
        $this->fire(Events::TEST_BEFORE, new TestEvent($this));
        $this->_before();
    }

    /**
     * @Override
     */
    protected function _before()
    {
    }

    protected function tearDown()
    {
        $this->_after();
        $this->fire(Events::TEST_AFTER, new TestEvent($this));
    }

    /**
     * @Override
     */
    protected function _after()
    {
    }

    public function getFeature()
    {
        $text = $this->getName();
        $text = preg_replace('/([A-Z]+)([A-Z][a-z])/', '\\1 \\2', $text);
        $text = preg_replace('/([a-z\d])([A-Z])/', '\\1 \\2', $text);
        return strtolower($text);
    }

    public function getSignature()
    {
        return get_class($this) . '::' . $this->getName(false);
    }

    public function getEnvironment()
    {
        return Annotation::forMethod($this, $this->getName(false))->fetchAll('env');
    }

    public function getFileName()
    {
        return (new \ReflectionClass($this))->getFileName();
    }

    /**
     * @param $module
     *
     * @return \Codeception\Module
     * @throws \Codeception\Exception\TestRuntimeException
     */
    public function getModule($module)
    {
        return $this->moduleContainer->getModule($module);
    }
}
