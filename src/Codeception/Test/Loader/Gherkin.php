<?php
namespace Codeception\Test\Loader;

use Behat\Gherkin\Filter\RoleFilter;
use Behat\Gherkin\Keywords\ArrayKeywords as GherkinKeywords;
use Behat\Gherkin\Lexer as GherkinLexer;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Parser as GherkinParser;
use Codeception\Test\Format\Gherkin as GherkinFormat;
use Codeception\Util\Annotation;

class Gherkin implements Loader
{
    protected static $defaultKeywords = [
        'feature'          => 'Feature',
        'background'       => 'Background',
        'scenario'         => 'Scenario',
        'scenario_outline' => 'Scenario Outline|Scenario Template',
        'examples'         => 'Examples|Scenarios',
        'given'            => 'Given',
        'when'             => 'When',
        'then'             => 'Then',
        'and'              => 'And',
        'but'              => 'But'
    ];

    protected $tests = [];

    /**
     * @var GherkinParser
     */
    protected $parser;

    protected $settings = [];

    protected $steps = [
        'default' => []
    ];

    public function __construct($settings)
    {
        $this->settings = $settings;
        $keywords = new GherkinKeywords(['en' => static::$defaultKeywords]);
        $lexer = new GherkinLexer($keywords);
        $this->parser = new GherkinParser($lexer);
        $this->fetchGherkinSteps();
    }

    protected function fetchGherkinSteps()
    {
        $contexts = $this->settings['contexts'];
        $this->addSteps($contexts['default']);
        foreach ($contexts['tag'] as $tag => $tagContexts) {
            $this->addSteps($tagContexts, "tag:$tag");
        }
        foreach ($contexts['role'] as $role => $roleContexts) {
            $this->addSteps($roleContexts, "role:$role");
        }
    }

    protected function addSteps(array $contexts, $group = 'default')
    {
        $this->steps[$group] = ['Given' => [], 'When' => [], 'Then' => []];
        foreach ($contexts as $context) {
            $methods = get_class_methods($context);
            foreach ($methods as $method) {
                $annotation = Annotation::forMethod($context, $method);
                foreach (['Given', 'When', 'Then'] as $type) {
                    $pattern = $annotation->fetch($type);
                    if (!$pattern) {
                        continue;
                    }
                    $pattern = $this->makePlaceholderPattern($pattern);
                    $this->steps[$group][$type][$pattern] = [$context, $method];
                }
            }
        }
    }

    protected function makePlaceholderPattern($pattern)
    {
        if (strpos($pattern, '/') !== 0) {
            $pattern = preg_replace('~:(\w+)~', '(\w+)', $pattern);
            $pattern = "/$pattern/";
        }
        return $pattern;
    }

    public function loadTests($filename)
    {
        $featureNode = $this->parser->parse(file_get_contents($filename));

        foreach ($featureNode->getScenarios() as $scenarioNode) {
            /** @var $scenarioNode ScenarioNode  **/
            $steps = $this->steps['default']; // load default context

            foreach ($scenarioNode->getTags() as $tag) { // load tag contexts
                if (isset($this->steps["tag:$tag"])) {
                    $steps = array_merge($steps, $this->steps["tag:$tag"]);
                }
            }

            $roles = $this->settings['contexts']['role']; // load role contexts
            foreach ($roles as $role) {
                $filter = new RoleFilter($role);
                if ($filter->isFeatureMatch($featureNode)) {
                    $steps = array_merge($steps, $this->steps["role:$role"]);
                    break;
                }
            }

            $this->tests[] = new GherkinFormat($featureNode, $scenarioNode, $steps);
        }
    }

    public function getTests()
    {
        return $this->tests;
    }

    public function getPattern()
    {
        return '~\.feature$~';
    }
}