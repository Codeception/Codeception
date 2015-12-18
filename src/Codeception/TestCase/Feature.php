<?php
namespace Codeception\TestCase;

use Behat\Gherkin\Keywords\ArrayKeywords as GherkinArrayKeywords;
use Behat\Gherkin\Lexer as GherkinLexer;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Parser as GherkinParser;
use Codeception\Lib\Di;
use Codeception\Lib\ModuleContainer;
use Codeception\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

class Feature extends \Codeception\Lib\Test implements
    TestCase,
    TestCase\Interfaces\ScenarioDriven,
    TestCase\Interfaces\Descriptive,
    TestCase\Interfaces\Configurable
{
    use TestCase\Shared\Actor;
    use TestCase\Shared\ScenarioPrint;

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