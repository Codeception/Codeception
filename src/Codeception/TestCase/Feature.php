<?php
namespace Codeception\TestCase;

use Behat\Gherkin\Keywords\ArrayKeywords as GherkinArrayKeywords;
use Behat\Gherkin\Lexer as GherkinLexer;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Parser as GherkinParser;

class Feature extends \Codeception\Test implements
    \Codeception\TestCase,
    Interfaces\ScenarioDriven,
    Interfaces\Descriptive,
    Interfaces\Configurable
{
    use Shared\Actor;
    use Shared\ScenarioPrint;

    /**
     * @var FeatureNode;
     */
    protected $feature;

    public function test()
    {

    }

    public function preload()
    {
        $keywords = new GherkinArrayKeywords(array(
            'en' => array(
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
            )
        ));
        $lexer  = new GherkinLexer($keywords);
        $parser = new GherkinParser($lexer);

        $this->feature = $parser->parse(file_get_contents(codecept_data_dir('refund.feature')));
    }


    public function getFileName()
    {
        // TODO: Implement getFileName() method.
    }

    public function getSignature()
    {
        // TODO: Implement getSignature() method.
    }

    public function getName()
    {
        // TODO: Implement getName() method.
    }

    function getRawBody()
    {
        // TODO: Implement getRawBody() method.
    }

    public function toString()
    {
        // TODO: Implement toString() method.
    }
}