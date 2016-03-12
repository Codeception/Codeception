<?php
namespace Codeception\TestCase;

use Codeception\Configuration;
use Codeception\TestCase as CodeceptionTestCase;
use Codeception\Util\Annotation;
use Codeception\TestCase\Interfaces\Descriptive;
use Codeception\TestCase\Interfaces\Reported;
use Codeception\TestCase\Interfaces\Configurable;
use Codeception\TestCase\Shared\Actor;
use Codeception\TestCase\Shared\Dependencies;

class Test extends CodeceptionTestCase implements
    Descriptive,
    Configurable,
    Reported
{
    use Actor;
    use Dependencies;

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
        $this->_before();
        $this->prepareActorForTest();
    }

    /**
     * Executed before each test
     */
    protected function _before()
    {
    }

    protected function tearDown()
    {
        $this->_after();
    }

    /**
     * Executed after each test
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

    /**
     * @return array
     */
    public function getReportFields()
    {
        return [
            'name'    => $this->getName(),
            'class'   => get_class($this),
            'file'    => $this->getFileName(),
            'feature' => $this->getFeature()
        ];
    }
}
