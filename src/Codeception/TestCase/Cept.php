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
    protected $trace = array();

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
        switch (strtolower($format))
        {
            case 'html':
                $text = '';
                foreach($this->scenario->getSteps() as $step) {
                    /** @var Step $step */
                    if ($step->getName() !== Step::COMMENT) {
                        $text .= 'I ' . $step->getHtmlAction() . '<br/>';
                    } else {
                        $text .= trim($step->getHumanizedArguments(), '"') . '<br/>';
                    }
                }
                $text = str_replace(array('((', '))'), array('...', ''), $text);
                $text = "<h3>" . strtoupper('I want to ' . $this->scenario->getFeature()) . "</h3>" . $text;
            break;

            default:
                $text = implode("\r\n", $this->scenario->getSteps());
                $text = str_replace(array('((', '))'), array('...', ''), $text);
                $text = strtoupper('I want to ' . $this->scenario->getFeature()) . "\r\n\r\n" . $text;
            break;
        }

        return $text;
    }

    public function getFeature() {
        return $this->scenario->getFeature();
    }

    public function toString()
    {
        return $this->scenario->getFeature() . ' (' . $this->getFileName() . ')';
    }

    public function getTrace()
    {
        return $this->trace;
    }
    
    public function testCodecept($run = true)
    {
        $scenario = $this->scenario;

        $this->preload();
        if (!$run) return;

        if (file_exists($this->bootstrap)) require $this->bootstrap;

        $this->dispatcher->dispatch('test.before', new \Codeception\Event\Test($this));
        $scenario->run();

        try {
            require $this->testfile;
        } catch (\PHPUnit_Framework_ExpectationFailedException $e) {
            $this->dispatcher->dispatch('test.fail', new \Codeception\Event\Fail($this, $e));
            throw $e;
        }
        $this->dispatcher->dispatch('test.after', new \Codeception\Event\Test($this));
    }

    protected function preload()
    {
        $scenario = $this->scenario;
        if (file_exists($this->bootstrap)) require $this->bootstrap;
        require $this->testfile;
    }
}
