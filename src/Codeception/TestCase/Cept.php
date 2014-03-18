<?php

namespace Codeception\TestCase;

use Codeception\Events;
use Codeception\Event\TestEvent;
use Codeception\Step;
use Codeception\TestCase;

class Cept extends TestCase implements Interfaces\ScenarioDriven, Interfaces\Descriptive, Interfaces\Reported, Interfaces\Plain
{
    use Shared\Actor;
    use Shared\ScenarioPrint;

    public function __construct(array $data = array(), $dataName = '')
    {
        parent::__construct('testCodecept', $data, $dataName);
    }

    public function getSignature()
    {
        return $this->name;
    }

    public function getName($withDataSet = true)
    {
        return $this->getFeature() ? $this->getFeature() : $this->name;
    }

    public function getFileName()
    {
        return $this->testFile;
    }

    public function toString()
    {
        return $this->getFeature(). " (".$this->getSignature().")";
    }

    public function preload()
    { 
        $this->parser->prepareToRun($this->getRawBody());
        $this->fire(Events::TEST_PARSED, new TestEvent($this));
    }

    public function getRawBody()
    {
        return file_get_contents($this->testFile);
    }

    public function testCodecept()
    {
        $this->fire(Events::TEST_BEFORE, new TestEvent($this));

        $scenario = $this->scenario;
        $scenario->run();

        /** @noinspection PhpIncludeInspection */
        require $this->testFile;

        $this->fire(Events::TEST_AFTER, new TestEvent($this));
    }

    public function getReportFields()
    {
        return ['name' => basename($this->getFileName(),'Cept.php'), 'file' => $this->getFileName()];
    }
}
