<?php
namespace Codeception\Test;

use Codeception\Exception\TestParseException;
use Codeception\Lib\Console\Message;
use Codeception\Lib\Parser;
use ParseError;
use function basename;
use function file_get_contents;

/**
 * Executes tests delivered in Cept format.
 * Prepares metadata, parses test body on preload, and executes a test in `test` method.
 */
class Cept extends Test implements Interfaces\Plain, Interfaces\ScenarioDriven, Interfaces\Reported, Interfaces\Dependent
{
    use Feature\ScenarioLoader;

    /**
     * @var Parser
     */
    protected $parser;

    public function __construct($name, $file)
    {
        $metadata = new Metadata();
        $metadata->setName($name);
        $metadata->setFilename($file);
        $this->setMetadata($metadata);
        $this->createScenario();
        $this->parser = new Parser($this->getScenario(), $this->getMetadata());
    }

    public function preload(): void
    {
        $this->getParser()->prepareToRun($this->getSourceCode());
    }

    public function test(): void
    {
        $testFile = $this->getMetadata()->getFilename();
        try {
            require $testFile;
        } catch (ParseError $error) {
            throw new TestParseException($testFile, $error->getMessage(), $error->getLine());
        }
    }

    public function getSignature(): string
    {
        return $this->getMetadata()->getName() . 'Cept';
    }

    public function toString(): string
    {
        return $this->getSignature() . ': ' . Message::ucfirst($this->getFeature());
    }

    /**
     * @return string|false
     */
    public function getSourceCode()
    {
        return file_get_contents($this->getFileName());
    }

    public function getReportFields(): array
    {
        return [
            'name' => basename($this->getFileName(), 'Cept.php'),
            'file' => $this->getFileName(),
            'feature' => $this->getFeature()
        ];
    }

    protected function getParser(): Parser
    {
        return $this->parser;
    }

    public function fetchDependencies(): array
    {
        return $this->getMetadata()->getDependencies();
    }
}
