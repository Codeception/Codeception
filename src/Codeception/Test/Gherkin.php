<?php

declare(strict_types=1);

namespace Codeception\Test;

use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\ScenarioInterface;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Node\StepNode;
use Behat\Gherkin\Node\TableNode;
use Codeception\Lib\Di;
use Codeception\Lib\Generator\GherkinSnippets;
use Codeception\Scenario;
use Codeception\Step\Comment;
use Codeception\Step\Meta;
use Codeception\Test\Interfaces\Reported;
use Codeception\Test\Interfaces\ScenarioDriven;
use Exception;

use function array_merge;
use function array_pop;
use function array_shift;
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

    public function __construct(protected FeatureNode $featureNode, protected ScenarioInterface $scenarioNode, protected array $steps = [])
    {
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
        $this->getMetadata()->setGroups($this->featureNode->getTags());
        $this->getMetadata()->setGroups($this->scenarioNode->getTags());
        $this->scenario->setMetaStep(null);

        if (($background = $this->featureNode->getBackground()) !== null) {
            foreach ($background->getSteps() as $step) {
                $this->validateStep($step);
            }
        }

        foreach ($this->scenarioNode->getSteps() as $step) {
            $this->validateStep($step);
        }
        if ($this->getMetadata()->getIncomplete()) {
            $this->getMetadata()->setIncomplete($this->getMetadata()->getIncomplete() . "\nRun gherkin:snippets to define missing steps");
        }
    }

    public function getSignature(): string
    {
        return basename($this->getFileName(), '.feature') . ':' . $this->getScenarioTitle();
    }

    public function test(): void
    {
        $this->makeContexts();
        $description = explode("\n", (string)$this->featureNode->getDescription());
        foreach ($description as $line) {
            $this->getScenario()->runStep(new Comment($line));
        }

        if (($background = $this->featureNode->getBackground()) !== null) {
            foreach ($background->getSteps() as $step) {
                $this->runStep($step);
            }
        }

        foreach ($this->scenarioNode->getSteps() as $step) {
            $this->runStep($step);
        }
    }

    protected function validateStep(StepNode $stepNode): void
    {
        $stepText = $stepNode->getText();
        if (GherkinSnippets::stepHasPyStringArgument($stepNode)) {
            $stepText .= ' ""';
        }
        $matches = [];
        foreach ($this->steps as $pattern => $context) {
            $res = preg_match($pattern, $stepText);
            if (!$res) {
                continue;
            }
            $matches[$pattern] = $context;
        }
        if ($matches === []) {
            // There were no matches, meaning that the user should first add a step definition for this step
            $incomplete = $this->getMetadata()->getIncomplete();
            $this->getMetadata()->setIncomplete("{$incomplete}\nStep definition for `{$stepText}` not found in contexts");
        }
        if (count($matches) > 1) {
            // There were more than one match, meaning that we don't know which step definition to execute for this step
            $incomplete = $this->getMetadata()->getIncomplete();
            $matchingDefinitions = [];
            foreach ($matches as $pattern => $context) {
                $matchingDefinitions[] = '- ' . $pattern . ' (' . self::contextAsString($context) . ')';
            }
            $this->getMetadata()->setIncomplete(
                "{$incomplete}\nAmbiguous step: `{$stepText}` matches multiple definitions:\n"
                . implode("\n", $matchingDefinitions)
            );
        }
    }

    private function contextAsString($context): string
    {
        if (is_array($context) && count($context) === 2) {
            [$class, $method] = $context;

            if (is_string($class) && is_string($method)) {
                return $class . ':' . $method;
            }
        }

        return var_export($context, true);
    }

    protected function runStep(StepNode $stepNode): void
    {
        $params = [];
        if ($stepNode->hasArguments()) {
            $args = $stepNode->getArguments();
            $table = $args[0];
            if ($table instanceof TableNode) {
                $params = [$table->getTableAsString()];
            }
        }
        $meta = new Meta($stepNode->getText(), $params);
        $meta->setPrefix($stepNode->getKeyword());

        $this->scenario->setMetaStep($meta); // enable metastep
        $stepText = $stepNode->getText();
        $hasPyStringArg = GherkinSnippets::stepHasPyStringArgument($stepNode);
        if ($hasPyStringArg) {
            // pretend it is inline argument
            $stepText .= ' ""';
        }
        $this->getScenario()->comment(''); // make metastep to be printed even if no steps in it
        foreach ($this->steps as $pattern => $context) {
            $matches = [];
            if (!preg_match($pattern, $stepText, $matches)) {
                continue;
            }
            array_shift($matches);
            if ($hasPyStringArg) {
                // get rid off last fake argument
                array_pop($matches);
            }
            if ($stepNode->hasArguments()) {
                $matches = array_merge($matches, $stepNode->getArguments());
            }
            call_user_func_array($context, $matches); // execute the step
            break;
        }
        $this->scenario->setMetaStep(null); // disable metastep
    }

    protected function makeContexts(): void
    {
        /** @var Di $di */
        $di = $this->getMetadata()->getService('di');
        $di->set($this->getScenario());

        $actorClass = $this->getMetadata()->getCurrent('actor');
        if ($actorClass) {
            $di->instantiate($actorClass);
        }

        foreach ($this->steps as $pattern => $step) {
            $di->instantiate($step[0]);
            $this->steps[$pattern][0] = $di->get($step[0]);
        }
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
            'name' => $this->toString(),
            'feature' => $this->getFeature(),
            'file' => $this->getFileName(),
        ];
    }
}
