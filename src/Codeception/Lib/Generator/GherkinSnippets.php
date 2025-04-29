<?php

declare(strict_types=1);

namespace Codeception\Lib\Generator;

use Behat\Gherkin\Node\StepNode;
use Codeception\Test\Loader\Gherkin;
use Codeception\Util\Template;
use Symfony\Component\Finder\Finder;

class GherkinSnippets
{
    protected string $template = <<<EOF
    /**
     * @{{type}} {{text}}
     */
     public function {{methodName}}({{params}})
     {
         throw new \PHPUnit\Framework\IncompleteTestError("Step `{{text}}` is not defined");
     }

EOF;

    /**
     * @var string[]
     */
    protected array $snippets = [];

    /**
     * @var string[]
     */
    protected array $processed = [];

    /**
     * @var string[]
     */
    protected array $features = [];

    public function __construct(array $settings, ?string $test = null)
    {
        $loader = new Gherkin($settings);
        $path = $settings['path'];
        $pattern = $loader->getPattern();
        if ($test) {
            $path = "$path/$test";
            if (preg_match($pattern, $test)) {
                $path = dirname($path);
                $pattern = basename($test);
            }
        }

        $finder = Finder::create()
            ->files()
            ->sortByName()
            ->in($path)
            ->followLinks()
            ->name($pattern);

        foreach ($finder as $file) {
            $loader->loadTests($file->getPathname());
        }
        $availableSteps = $loader->getSteps();
        $allSteps = [];
        foreach ($availableSteps as $stepGroup) {
            $allSteps = array_merge($allSteps, $stepGroup);
        }
        foreach ($loader->getTests() as $test) {
            $steps = $test->getScenarioNode()->getSteps();
            if ($test->getFeatureNode()->hasBackground()) {
                $steps = array_merge($steps, $test->getFeatureNode()->getBackground()->getSteps());
            }
            foreach ($steps as $step) {
                $matched = false;
                $text = $step->getText();
                if (self::stepHasPyStringArgument($step)) {
                    // pretend it is inline argument
                    $text .= ' ""';
                }
                foreach (array_keys($allSteps) as $pattern) {
                    if (preg_match($pattern, $text)) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $this->addSnippet($step);
                    $file = str_ireplace($settings['path'], '', $test->getFeatureNode()->getFile());
                    if (!in_array($file, $this->features)) {
                        $this->features[] = $file;
                    }
                }
            }
        }
    }

    public function addSnippet(StepNode $step): void
    {
        $args = [];
        $pattern = $step->getText();

        // match numbers (not in quotes)
        $pattern = preg_replace_callback('#([\d.])(?=([^"]*"[^"]*")*[^"]*$)#', function () use (&$args): string {
            $args[] = '$num' . (count($args) + 1);
            return ":num" . count($args);
        }, $pattern);

        // match quoted strings
        $pattern = preg_replace_callback('#"(.*?)"#', function () use (&$args): string {
            $args[] = '$arg' . (count($args) + 1);
            return ":arg" . count($args);
        }, $pattern);

        // add multiline argument if present
        if (self::stepHasPyStringArgument($step)) {
            $args[] = '$arg' . (count($args) + 1);
            $pattern .= " :arg" . count($args);
        }

        if (in_array($pattern, $this->processed)) {
            return;
        }

        $methodName = preg_replace('#(\s+?|\'|\"|\W)#', '', ucwords(preg_replace('#"(.*?)"|\d+#', '', $step->getText())));
        $methodName = empty($methodName) ? 'step_' . substr(sha1($pattern), 0, 9) : lcfirst($methodName);

        $this->snippets[] = (new Template($this->template))
            ->place('type', $step->getKeywordType())
            ->place('text', $pattern)
            ->place('methodName', $methodName)
            ->place('params', implode(', ', $args))
            ->produce();

        $this->processed[] = $pattern;
    }

    /**
     * @return string[]
     */
    public function getSnippets(): array
    {
        return $this->snippets;
    }

    /**
     * @return string[]
     */
    public function getFeatures(): array
    {
        return $this->features;
    }

    public static function stepHasPyStringArgument(StepNode $step): bool
    {
        if ($step->hasArguments()) {
            $stepArgs = $step->getArguments();
            return end($stepArgs)->getNodeType() === 'PyString';
        }
        return false;
    }
}
