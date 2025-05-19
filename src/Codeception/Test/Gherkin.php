<?php

declare(strict_types=1);

namespace Codeception\Test;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Codeception\Lib\Generator\GherkinSnippets;
use Codeception\Scenario;
use Codeception\Step\Comment;
use Codeception\Step\Meta;
use Codeception\Test\Interfaces\Reported;
use Codeception\Test\Interfaces\ScenarioDriven;
use Exception;

use function array_keys;
use function array_merge;
use function basename;
use function call_user_func_array;
use function count;
use function explode;
use function file_get_contents;
use function is_array;
use function is_string;
use function preg_match;
use function var_export;

class Gherkin extends Test implements ScenarioDriven, Reported
{
    protected Scenario $scenario;

    public function __construct(
        protected FeatureNode $featureNode,
        protected ScenarioInterface $scenarioNode,
        protected array $steps = []
    ) {
        $this->setMetadata(new Metadata());
        $this->scenario = new Scenario($this);
        $this->getMetadata()->setName($this->scenarioNode->getTitle());
        $this->getMetadata()->setFeature((string)$this->featureNode->getTitle());
        $this->getMetadata()->setFilename($this->featureNode->getFile());
    }

    public function __clone(): void
    {
        $this->scenario = clone $this->scenario;
    }

    public function preload(): void
    {
        $metadata = $this->getMetadata();
        $metadata->setGroups(array_merge($this->featureNode->getTags(), $this->scenarioNode->getTags()));
        $this->scenario->setMetaStep(null);
        $this->processSteps([$this, 'validateStep']);

        if ($incomplete = rtrim((string) $metadata->getIncomplete(), "\n")) {
            $metadata->setIncomplete($incomplete . "\nRun gherkin:snippets to define missing steps");
        }
    }

    public function getSignature(): string
    {
        return basename($this->getFileName(), '.feature') . ':' . $this->getScenarioTitle();
    }

    public function test(): void
    {
        $this->makeContexts();
        foreach (explode("\n", (string)$this->featureNode->getDescription()) as $line) {
            $this->scenario->runStep(new Comment($line));
        }
        $this->processSteps([$this, 'runStep']);
    }

    private function processSteps(callable $callback): void
    {
        if ($background = $this->featureNode->getBackground()) {
            array_map($callback, $background->getSteps());
        }
        array_map($callback, $this->scenarioNode->getSteps());
    }

    protected function validateStep(StepNode $stepNode): void
    {
        $text = $stepNode->getText() . (GherkinSnippets::stepHasPyStringArgument($stepNode) ? ' ""' : '');
        $metadata = $this->getMetadata();

        $matchedPatterns = array_filter(
            array_keys($this->steps),
            fn(string $pattern): bool => preg_match($pattern, $text) === 1
        );

        if ($matchedPatterns === []) {
            $metadata->setIncomplete(
                ($metadata->getIncomplete() ?? '')
                . "\nStep definition for `{$text}` not found in contexts"
            );
        } elseif (count($matchedPatterns) > 1) {
            $defs = array_map(
                fn(string $pattern): string => "- {$pattern} ({$this->contextAsString($this->steps[$pattern])})",
                $matchedPatterns
            );
            $metadata->setIncomplete(
                ($metadata->getIncomplete() ?? '')
                . "\nAmbiguous step: `{$text}` matches multiple definitions:\n"
                . implode("\n", $defs)
            );
        }
    }

    protected function runStep(StepNode $stepNode): void
    {
        $text = $stepNode->getText() . (GherkinSnippets::stepHasPyStringArgument($stepNode) ? ' ""' : '');
        $params = [];
        if ($stepNode->hasArguments() && $stepNode->getArguments()[0] instanceof TableNode) {
            $params[] = $stepNode->getArguments()[0]->getTableAsString();
        }
        $meta = new Meta($stepNode->getText(), $params);
        $meta->setPrefix($stepNode->getKeyword());
        $this->scenario->setMetaStep($meta);
        $this->scenario->comment('');

        foreach ($this->steps as $pattern => $context) {
            if (!preg_match($pattern, $text, $matches)) {
                continue;
            }
            $args = array_slice($matches, 1);
            if (GherkinSnippets::stepHasPyStringArgument($stepNode)) {
                array_pop($args);
            }
            if ($stepNode->hasArguments()) {
                $args = array_merge($args, $stepNode->getArguments());
            }
            call_user_func_array($context, $args);
            break;
        }

        $this->scenario->setMetaStep(null);
    }

    protected function makeContexts(): void
    {
        $di = $this->getMetadata()->getService('di');
        $di->set($this->scenario);
        if ($actor = $this->getMetadata()->getCurrent('actor')) {
            $di->instantiate($actor);
        }
        foreach ($this->steps as $pattern => &$step) {
            $di->instantiate($step[0]);
            $step[0] = $di->get($step[0]);
        }
    }

    private function contextAsString(mixed $context): string
    {
        if (is_array($context) && count($context) === 2) {
            [$class, $method] = $context;
            if (is_string($class) && is_string($method)) {
                return "{$class}:{$method}";
            }
        }
        return var_export($context, true);
    }

    public function toString(): string
    {
        return $this->getFeature() . ': ' . $this->getScenarioTitle();
    }

    public function getFeature(): string
    {
        return $this->getMetadata()->getFeature();
    }

    public function getScenarioTitle(): string
    {
        return $this->getMetadata()->getName();
    }

    public function getScenario(): Scenario
    {
        return $this->scenario;
    }

    public function getScenarioText(string $format = 'text'): string
    {
        $fileName = $this->getFileName();
        if (!$scenarioText = file_get_contents($fileName)) {
            throw new Exception("Could not get scenario {$fileName}, please check its permissions.");
        }
        return $scenarioText;
    }

    public function getSourceCode(): string
    {
        return '';
    }

    public function getScenarioNode(): ScenarioNode
    {
        return $this->scenarioNode;
    }

    public function getFeatureNode(): FeatureNode
    {
        return $this->featureNode;
    }

    /**
     * Field values for XML reports
     *
     * @return array<string, string>
     */
    public function getReportFields(): array
    {
        return [
            'name'    => $this->toString(),
            'feature' => $this->getFeature(),
            'file'    => $this->getFileName(),
        ];
    }
}
