<?php
namespace Codeception\Module;

use Codeception\TestCase;
use League\FactoryMuffin\FactoryMuffin;

class DataFactory extends \Codeception\Module
{

    /**
     * @var FactoryMuffin
     */
    protected $factoryMuffin;

    protected $config = ['load' => null];

    public function _before(TestCase $test)
    {
        $this->factoryMuffin = new FactoryMuffin();

        if ($this->config['factories']) {
            $this->factoryMuffin->loadFactories($this->config['factories']);
        }
    }

    public function _define($model, $fields)
    {
        return $this->factoryMuffin->define($model)->setDefinitions($fields);
    }

    public function haveModel($name, $extraFields = [])
    {
        $this->getModule('Phalcon')
    }

}