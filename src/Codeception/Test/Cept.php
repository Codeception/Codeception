<?php

declare(strict_types=1);

namespace Codeception\Test;

use Codeception\Exception\TestParseException;
use Codeception\Lib\Console\Message;
use Codeception\Lib\Parser;
use ParseError;
use RuntimeException;

use function basename;
use function file_get_contents;

/**
 * Executes tests delivered in Cept format.
 * Prepares metadata, parses test body on preload, and executes a test in `test` method.
 *
 * Note: If the time came to delete Cept format, please delete Actor::wantTo method too
 */
class Cept extends Test implements Interfaces\Plain, Interfaces\ScenarioDriven, Interfaces\Reported, Interfaces\Dependent
{
    use Feature\ScenarioLoader;

    protected Parser $parser;

    public function __construct(string $name, string $file)
    {
        $metadata = new Metadata();
        $metadata->setName($name);
        $metadata->setFilename($file);
        $this->setMetadata($metadata);
        $this->createScenario();
        $this->parser = new Parser($this->getScenario(), $this->getMetadata());
    }

    public function __clone(): void
    {
        $this->scenario = clone $this->scenario;
    }

    public function preload(): void
    {
        $this->getParser()->prepareToRun($this->getSourceCode());
    }

    public function test(): void
    {
        $scenario = $this->getScenario();
        try {
            require $this->getFileName();
        } catch (ParseError $error) {
            throw new TestParseException($this->getFileName(), $error->getMessage(), $error->getLine());
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

    public function getSourceCode(): string
    {
        $fileName = $this->getFileName();
        if (!is_readable($fileName) || ($sourceCode = file_get_contents($fileName)) === false) {
            throw new RuntimeException("Could not read file {$fileName}, please check its permissions.");
        }
        return $sourceCode;
    }

    /**
     * @return array<string, string>
     */
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
