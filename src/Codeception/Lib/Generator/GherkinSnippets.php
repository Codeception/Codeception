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
     }
EOF;

    protected $snippets = [];

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
            foreach ($steps as $step) {
                $matched = false;
                $text = $step->getText();
                foreach (array_keys($allSteps) as $pattern) {
                    if (preg_match($pattern, $text)) {
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
        if (preg_match_all('~"(.*?)"~', $pattern, $matches)) {
            foreach ($matches[1] as $num => $param) {
                $num++;
                $args[] = '$arg' . $num;
                $pattern = str_replace('"'.$param.'"', ":arg$num" , $pattern);
            }
        }

        $methodName = preg_replace('~(\s+?|\'|\")~', '', ucwords(preg_replace('~"(.*?)"~', '', $step->getText())));

        $this->snippets[] = (new Template($this->template))
            ->place('type', $step->getKeywordType())
            ->place('text', $pattern)
            ->place('methodName', lcfirst($methodName))
            ->place('params', implode(', ', $args))
            ->produce();
    }

    public function getSnippets()
    {
        return $this->snippets;
    }
}