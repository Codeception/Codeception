<?php

namespace Codeception\TestCase;

use Codeception\CodeceptionEvents;
use Codeception\Event\TestEvent;
use Codeception\Scenario;
use Codeception\Step;
use Codeception\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Cept extends TestCase implements ScenarioDriven
{
    private $name;
    protected $testFile = null;
    protected $output;
    protected $debug;
    protected $features = array();
    protected $bootstrap = null;
    protected $stopped = false;

    public function __construct(EventDispatcher $dispatcher, array $data = array(), $dataName = '')
    {
        parent::__construct('testCodecept', $data, $dataName);

        $this->dispatcher = $dispatcher;

        if (! isset($data['file'])) {
            throw new \Exception('File with test scenario not set. Use array(file => filepath) to set a scenario');
        }

        $this->name      = $data['name'];
        $this->testFile  = $data['file'];
        $this->scenario  = new Scenario($this);

        if (isset($data['bootstrap']) && file_exists($data['bootstrap'])) {
            $this->bootstrap = $data['bootstrap'];
        }
    }

    public function getFileName()
    {
        return $this->name;
    }

    public function getName($withDataSet = true)
    {
        return $this->name;
    }

    public function getScenarioText($format = 'text')
    {
        if ($format == 'html') {
            return $this->scenario->getHtml();
        }
        return $this->scenario->getText();
    }

    public function getFeature()
    {
        return $this->scenario->getFeature();
    }

    public function toString()
    {
        return $this->scenario->getFeature() . ' (' . $this->getFileName() . ')';
    }

    public function preload()
    { 
        $body = $this->getRawBody();
        $this->setTitle();
        $this->runScenario();
        $this->fire(CodeceptionEvents::TEST_PARSED, new TestEvent($this));
    }

    public function getRawBody()
    {
        return file_get_contents($this->testFile);
    }
    
    protected function getFeatureTitle($body)
    {


    }

    public function testCodecept()
    {
        $this->fire(CodeceptionEvents::TEST_BEFORE, new TestEvent($this));

        $scenario = $this->scenario;
        $scenario->run();
        if ($this->bootstrap) {
            /** @noinspection PhpIncludeInspection */
            require $this->bootstrap;
        }
        /** @noinspection PhpIncludeInspection */
        require $this->testFile;

        $this->fire(CodeceptionEvents::TEST_AFTER, new TestEvent($this));
    }
}
