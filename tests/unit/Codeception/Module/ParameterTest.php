<?php
namespace Codeception\tests\unit\Codeception\Module;

use Codeception\Module\Parameter;

/**
 * Class ParameterTest
 */
class ParameterTest extends \PHPUnit_Framework_TestCase
{
    /** @var Parameter */
    protected $module;

    protected $parameterList = [
        'foo' => 'bar',
        'bar' => 'beer',
        'beer' => 'bong'
    ];

    public function setUp()
    {
        $parameterList = $this->parameterList;
        $this->module = new \Codeception\Module\Parameter(
            \Codeception\Util\Stub::make(
                'Codeception\Lib\ModuleContainer',
                [
                    'getParameter' => function ($key) use ($parameterList) {
                        return $parameterList[$key];
                    }
                ]
            )
        );
    }

    public function test()
    {
        foreach ($this->parameterList as $key => $value) {
            $this->assertEquals($value, $this->module->getParameter($key));
        }
    }
}
