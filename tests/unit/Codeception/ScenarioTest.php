<?php

class ScenarioTest extends \PHPUnit_Framework_TestCase
{
    public function testGetHtml()
    {
        $step1 = $this->getMockBuilder('\Codeception\Step')
            ->setConstructorArgs(['Do some testing', ['arg1', 'arg2']])
            ->setMethods(null)
            ->getMock();
        $step2 = $this->getMockBuilder('\Codeception\Step')
            ->setConstructorArgs(['Do even more testing without args', []])
            ->setMethods(null)
            ->getMock();

        $scenario = new \Codeception\Scenario(new \Codeception\Test\Cept('test', 'testCept.php'));
        $scenario->addStep($step1);
        $scenario->addStep($step2);
        $scenario->setFeature('Do some testing');

        $this->assertSame(
            '<h3>I WANT TO DO SOME TESTING</h3>I do some testing <span style="color: #732E81">"arg1","arg2"</span>'
            . '<br/>I do even more testing without args<br/>',
            $scenario->getHtml()
        );
    }
}
