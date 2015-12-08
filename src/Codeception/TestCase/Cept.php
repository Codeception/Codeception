<?php
namespace Codeception\TestCase;

use Codeception\Event\TestEvent;
use Codeception\Events;
use Codeception\Exception\TestParseException;
use Codeception\TestCase as CodeceptionTestCase;
use Codeception\TestCase\Interfaces\ScenarioDriven;
use Codeception\TestCase\Interfaces\Descriptive;
use Codeception\TestCase\Interfaces\Reported;
use Codeception\TestCase\Interfaces\Plain;
use Codeception\TestCase\Interfaces\Configurable;
use Codeception\TestCase\Shared\Actor;
use Codeception\TestCase\Shared\ScenarioPrint;

class Cept extends CodeceptionTestCase implements
    ScenarioDriven,
    Descriptive,
    Reported,
    Plain,
    Configurable
{
    use Actor;
    use ScenarioPrint;

    public function __construct(array $data = [], $dataName = '')
    {
        parent::__construct('testCodecept', $data, $dataName);
    }

    public function getSignature()
    {
        return ltrim(substr($this->testName, 0, -4), '\\/'); // cut ".php" in end; cut "/" in start
    }

    public function getName($withDataSet = true)
    {
        return $this->getFeature() ? $this->getFeature() : $this->testName;
    }

    public function getFileName()
    {
        return $this->testFile;
    }

    public function toString()
    {
        return $this->getFeature() . " (" . $this->getSignature() . ")";
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
        $scenario = $this->scenario;

        $this->prepareActorForTest();

        /** @noinspection PhpIncludeInspection */
        try {
            require $this->testFile;
        } catch (\ParseError $e) {
            throw new TestParseException($this->testFile);
        }
    }

    public function getEnvironment()
    {
        return $this->scenario->getEnv();
    }

    public function getReportFields()
    {
        return [
            'name' => basename($this->getFileName(), 'Cept.php'),
            'file' => $this->getFileName(),
            'feature' => $this->getFeature()
        ];
    }
}
