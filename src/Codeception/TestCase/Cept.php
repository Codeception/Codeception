<?php
namespace Codeception\TestCase;

use Codeception\Event\Fail;
use Codeception\Event\Test as TestEvent;
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
        $this->bootstrap = isset($data['bootstrap']) ? $data['bootstrap'] : null;
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

    public function preload()
    {
        $scenario = $this->scenario;
        // preload
        if (file_exists($this->bootstrap)) require $this->bootstrap;
        require $this->testfile;

        $this->fire('test.parsed', new TestEvent($this));
    }

    public function testCodecept()
    {
        $scenario = $this->scenario;


        $scenario->run();
        if (file_exists($this->bootstrap)) require $this->bootstrap;

        try {
            require $this->testfile;
        } catch (\Exception $e) {
            // fails and errors are now handled by Codeception\PHPUnit\Listener
            throw $e;
        }
    }

}
