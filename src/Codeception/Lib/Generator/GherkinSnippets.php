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
         throw new \PHPUnit\Framework\IncompleteTestError("Step `{{text}}` is not defined");
     }

EOF;

    protected $snippets = [];
    protected $processed = [];
    protected $features = [];

    public function __construct($settings, $test = null)
    {
        $loader = new Gherkin($settings);
        $pattern = $loader->getPattern();
        $path = $settings['path'];
        if (!empty($test)) {
            $path = $settings['path'].'/'.$test;
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
        // Has multiline argument at the end of step?
        if (self::stepHasPyStringArgument($step)) {
            $num = count($args) + 1;
            $pattern .= " :arg$num";
            $args[] = '$arg' . $num;
        }
        if (in_array($pattern, $this->processed)) {
            return;
        }

        $methodName = preg_replace('~(\s+?|\'|\"|\W)~', '', ucwords(preg_replace('~"(.*?)"|\d+~', '', $step->getText())));
        if (empty($methodName)) {
            $methodName = 'step_' . substr(sha1($pattern), 0, 9);
        }

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

    public function getFeatures()
    {
        return $this->features;
    }

    public static function stepHasPyStringArgument(StepNode $step)
    {
        if ($step->hasArguments()) {
            $stepArgs = $step->getArguments();
            if ($stepArgs[count($stepArgs) - 1]->getNodeType() == "PyString") {
                return true;
            }
        }
        return false;
    }
}
