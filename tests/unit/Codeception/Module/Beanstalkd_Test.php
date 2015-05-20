<?php

use Codeception\Util\Stub;

class Beanstalkd_Test extends \PHPUnit_Framework_TestCase
{
    protected $config = array(
        'type' => 'beanstalkq',
        'host' => 'localhost'
    );

    /**
     * @var \Codeception\Module\Queue
     */
    protected $module = null;

    public function setUp()
    {
        $this->module = new \Codeception\Module\Queue();
            $this->module->_setConfig($this->config);
            $this->module->_before(Stub::make('\Codeception\TestCase'));
        try {
            $this->module->clearQueue('default');
        } catch (\Pheanstalk_Exception_ConnectionException $e) {
            $this->markTestSkipped("Beanstalk is not running");
        }
    }

    /** @test */
    public function flow()
    {
        $this->module->addMessageToQueue('hello world - ' . date('d-m-y'), 'default');
        $this->module->clearQueue('default');

        $this->module->seeQueueExists('default');
        $this->module->dontSeeQueueExists('fake_queue');

        $this->module->seeEmptyQueue('default');
        $this->module->addMessageToQueue('hello world - ' . date('d-m-y'), 'default');
        $this->module->dontSeeEmptyQueue('default');

        $this->module->seeQueueHasTotalCount('default', 2);

        $this->module->seeQueueHasCurrentCount('default', 1);
        $this->module->dontSeeQueueHasCurrentCount('default', 9999);

        $this->module->grabQueues();
    }
}