<?php
namespace Codeception\Test\Loader;

use Behat\Gherkin\Keywords\ArrayKeywords as GherkinKeywords;
use Behat\Gherkin\Lexer as GherkinLexer;
use Behat\Gherkin\Parser as GherkinParser;
use Codeception\Test\Format\Gherkin as GherkinFormat;

class Gherkin implements Loader
{
    protected $tests = [];

    /**
     * @var GherkinParser
     */
    protected $parser;

    public function __construct()
    {
        $keywords = new GherkinKeywords(['en' => $this->defaultKeywords]);
        $lexer = new GherkinLexer($keywords);
        $this->parser = new GherkinParser($lexer);
    }

    protected $defaultKeywords = [
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

    public function loadTests($filename)
    {
        $featureNode = $this->parser->parse(file_get_contents($filename));
        foreach ($featureNode->getScenarios() as $scenarioNode) {
            $this->tests[] = new GherkinFormat($featureNode, $scenarioNode);
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