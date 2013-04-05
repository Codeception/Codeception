<?php
namespace Codeception\TestCase;

use Symfony\Component\EventDispatcher\EventDispatcher;
use Codeception\Step;

class Cept extends \Codeception\TestCase
{
    private $name;
    protected $testfile = null;
    protected $output;
    protected $debug;
    protected $features = array();
    protected $bootstrap = null;
    protected $stopped = false;

    protected $dispatcher;


    public function __construct(EventDispatcher $dispatcher, array $data = array(), $dataName = '')
    {
        parent::__construct('testCodecept', $data, $dataName);
        $this->dispatcher = $dispatcher;

        if (!isset($data['file'])) throw new \Exception('File with test scenario not set. Use array(file => filepath) to set a scenario');

        $this->name = $data['name'];
        $this->scenario = new \Codeception\Scenario($this);
        $this->testfile = $data['file'];
        $this->bootstrap = $data['bootstrap'] ? $data['bootstrap'] : null;
    }

    public function getFileName()
    {
        return $this->name;
    }

    public function getScenarioText($format = 'text')
    {
        if ($format == 'html') return $this->scenario->getHtml();
        return $this->scenario->getText();
    }

    public function getFeature() {
        return $this->scenario->getFeature();
    }

    public function toString()
    {
        return $this->scenario->getFeature() . ' (' . $this->getFileName() . ')';
    }

    public function testCodecept($run = true)
    {
        $scenario = $this->scenario;

        // preload
        if (file_exists($this->bootstrap)) require $this->bootstrap;
        require $this->testfile;

        if (!$run) return;
        $this->dispatcher->dispatch('test.parsed', new \Codeception\Event\Test($this));

        if (file_exists($this->bootstrap)) require $this->bootstrap;

        $scenario->run();
        $this->dispatcher->dispatch('test.before', new \Codeception\Event\Test($this));
        try {
            require $this->testfile;
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->dispatcher->dispatch('test.fail', new \Codeception\Event\Fail($this, $e));
            throw $e;
        } catch (\Exception $e) {
            $this->dispatcher->dispatch('test.after', new \Codeception\Event\Test($this));
            throw $e;
        }
        $this->dispatcher->dispatch('test.after', new \Codeception\Event\Test($this));
    }

}
