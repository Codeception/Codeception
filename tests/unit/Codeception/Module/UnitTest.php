<?php
use \Codeception\Util\Stub as Stub;

class UnitTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \Codeception\Module\Unit
     */
    protected $module;

    public function setUp()
    {
        require_once \Codeception\Configuration::dataDir().'/services/UserModel.php';
        require_once \Codeception\Configuration::dataDir().'/services/UserService.php';

        $this->test = $this->makeTest();
        $this->scenario = $this->test->getScenario();

        $this->module = new \Codeception\Module\Unit;
        $this->module->_initialize();
        $this->module->_before($this->test);


    }

    function tearDown()
    {
        if ($this->module) $this->module->_after($this->test);
    }

    protected function makeTest()
    {
        return Stub::make('\Codeception\TestCase', array('scenario' => Stub::make('\Codeception\Scenario'), 'dispatcher' => Stub::makeEmpty('Symfony\Component\EventDispatcher\EventDispatcher')));
    }

    protected function runSteps()
    {
        foreach ($this->scenario->getSteps() as $step)
        {
            $action = $step->getAction();
            $arguments = $step->getArguments();
            call_user_func_array(array($this->module, $action), $arguments);
            Stub::update($this->scenario, array('currentStep' => $this->scenario->getCurrentStep()+1));
        }
    }

    function testExecute()
    {
        $I = new CodeGuy($this->scenario);
        $I->execute(function () {
            PHPUnit_Framework_Assert::assertTrue(true);
            return true;
        });
        $I->seeResultEquals(true);
        $this->runSteps();
    }

    function testStaticExecuteTestedMethod()
    {
        $I = new CodeGuy($this->scenario);
        $I->testMethod('UserService::validateName');
        $I->executeTestedMethodWith('davert');
        $I->seeResultEquals(true);

        $I->executeTestedMethod('admin');
        $I->seeResultEquals(false);

        $this->runSteps();
    }

    function testExecuteTestedMethod()
    {
        $I = new CodeGuy($this->scenario);
        $user = new \UserModel;
        $I->testMethod('UserModel.save');
        $I->executeTestedMethodOn($user);
        $I->seeResultEquals(true);
        $this->runSteps();
    }

    function testMocks()
    {
        $this->markTestSkipped();
        $I = new CodeGuy($this->scenario);
        $I->testMethod('UserService.create');
        $I->haveFakeClass($user = Stub::makeEmpty('UserModel'));
        $service = new UserService($user);
        $I->executeTestedMethodOn($service,'davert');
        $I->seeMethodInvoked($user, 'save');
        $I->seeMethodInvokedOnce($user, 'save');
        $I->seeMethodInvokedMultipleTimes($user,'set',2);
        $I->seeMethodNotInvoked($user,'get');
        $this->runSteps();
    }

    function testExceptions()
    {
        $I = new CodeGuy($this->scenario);
        $I->testMethod('UserModel.get');
        $user = new UserModel();
        $I->executeTestedMethodOn($user,'name');
        $I->seeExceptionThrown('Exception');
        $this->runSteps();
    }



}
