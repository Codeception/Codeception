<?php
namespace Codeception\Lib\Generator;

use Behat\Gherkin\Node\StepNode;
use Codeception\Test\Loader\Gherkin;
use Codeception\Util\Template;
use Symfony\Component\Finder\Finder;

class GherkinSnippets
{
    protected $template = <<<EOF
    /**
     * @{{type}} {{text}}
     */
     public function {{methodName}}({{params}})
     {
        throw new \Codeception\Exception\Incomplete("Step `{{text}}` is not defined");
     }
     
EOF;

    protected $snippets = [];
    protected $processed = [];

    public function __construct($settings)
    {
        $loader = new Gherkin($settings);

        $finder = Finder::create()
            ->files()
            ->sortByName()
            ->in($settings['path'])
            ->followLinks()
            ->name($loader->getPattern());

        foreach ($finder as $file) {
            $pathname = str_replace("//", "/", $file->getPathname());
            $loader->loadTests($pathname);
        }
        $availableSteps = $loader->getSteps();
        $allSteps = [];
        foreach ($availableSteps as $stepGroup) {
            $allSteps = array_merge($allSteps, $stepGroup);
        }
        foreach ($loader->getTests() as $test) {
            /** @var $test \Codeception\Test\Gherkin  **/
            $steps = $test->getScenarioNode()->getSteps();
            if ($test->getFeatureNode()->hasBackground()) {
                $steps = array_merge($steps, $test->getFeatureNode()->getBackground()->getSteps());
            }
            foreach ($steps as $step) {
                $matched = false;
                $text = $step->getText();
                foreach (array_keys($allSteps) as $pattern) {
                    if (preg_match($pattern, $text)) {
                        echo "$pattern -> $text \n";
                        $matched = true;
                        break;
                    }
                }
                if (!$matched) {
                    $this->addSnippet($step);
                }
            }
        }
    }

    public function addSnippet(StepNode $step)
    {
        $args = [];
        $pattern = $step->getText();

        // match numbers (not in quotes)
        if (preg_match_all('~([\d\.])(?=([^"]*"[^"]*")*[^"]*$)~', $pattern, $matches)) {
            foreach ($matches[1] as $num => $param) {
                $num++;
                $args[] = '$num' . $num;
                $pattern = str_replace($param, ":num$num", $pattern);
            }
        }

        // match quoted string
        if (preg_match_all('~"(.*?)"~', $pattern, $matches)) {
            foreach ($matches[1] as $num => $param) {
                $num++;
                $args[] = '$arg' . $num;
                $pattern = str_replace('"'.$param.'"', ":arg$num", $pattern);
            }
        }
        if (in_array($pattern, $this->processed))  {
            return;
        }

        $methodName = preg_replace('~(\s+?|\'|\"|\W)~', '', ucwords(preg_replace('~"(.*?)"|\d+~', '', $step->getText())));

        $this->snippets[] = (new Template($this->template))
            ->place('type', $step->getKeywordType())
            ->place('text', $pattern)
            ->place('methodName', lcfirst($methodName))
            ->place('params', implode(', ', $args))
            ->produce();

        $this->processed[] = $pattern;
    }

    public function getSnippets()
    {
        return $this->snippets;
    }
}
