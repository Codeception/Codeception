<?php
namespace Codeception\Test\Format;

use Codeception\Exception\TestParseException;
use Codeception\Lib\Parser;
use Codeception\Test\Feature\ScenarioLoader;
use Codeception\Test\Feature\ScenarioRunner;
use Codeception\Test\Interfaces\Plain;
use Codeception\Test\Interfaces\Reported;
use Codeception\Test\Interfaces\ScenarioDriven;

class Cept extends \Codeception\Test\Test implements Plain, ScenarioDriven, Reported
{
    use ScenarioRunner;
    use ScenarioLoader;

    /**
     * @var Parser
     */
    protected $parser;

    public function __construct($name, $file)
    {
        $this->getMetadata()->setName($name);
        $this->getMetadata()->setFilename($file);
        $this->createScenario();
        $this->parser = new Parser($this->getScenario(), $this->getMetadata());
    }

    public function preload()
    {
        $this->getParser()->prepareToRun($this->getRawBody());
    }

    public function test()
    {
        $scenario = $this->getScenario();
        $testFile = $this->getMetadata()->getFilename();
        /** @noinspection PhpIncludeInspection */
        try {
            require $testFile;
        } catch (\ParseError $e) {
            throw new TestParseException($testFile);
        }
    }

    public function getSignature()
    {
        return $this->getName();
    }

    public function getName()
    {
        return $this->getFeature()
            ? $this->getFeature()
            : $this->getMetadata()->getName() . 'Cept';
    }

    public function toString()
    {
        return $this->getFeature() . " (" . $this->getSignature() . ")";
    }

    public function getRawBody()
    {
        return file_get_contents($this->getFileName());
    }

    public function getReportFields()
    {
        return [
            'name' => basename($this->getFileName(), 'Cept.php'),
            'file' => $this->getFileName(),
            'feature' => $this->getFeature()
        ];
    }

    /**
     * @return Parser
     */
    protected function getParser()
    {
        return $this->parser;
    }
}
