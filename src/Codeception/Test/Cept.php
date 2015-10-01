<?php
namespace Codeception\Test;

use Codeception\Exception\TestParseException;
use Codeception\Lib\Parser;

class Cept extends Test implements Interfaces\Plain, Interfaces\ScenarioDriven, Interfaces\Reported
{
    use Feature\ScenarioLoader;

    /**
     * @var Parser
     */
    protected $parser;

    public function __construct($name, $file)
    {
        $this->setMetadata(new Metadata());
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
        return $this->getMetadata()->getName() . 'Cept';
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
