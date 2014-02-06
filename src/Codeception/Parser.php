<?php
namespace Codeception;

use Codeception\Util\Debug;

class Parser {

    protected $scenario;
    protected $code;

    public function __construct(Scenario $scenario)
    {
        $this->scenario = $scenario;
    }

    public function prepareToRun($code)
    {
        $this->parseFeature($code);
        $this->parseScenarioOptions($code);
    }

    public function parseFeature($code)
    {
        $matches = array();
        $res = preg_match("~\\\$I->wantTo\\(['\"](.*?)['\"]\\);~", $code, $matches);
        if ($res) {
            $this->scenario->setFeature($matches[1]);
            return;
        }
        $res = preg_match("~\\\$I->wantToTest\\(['\"](.*?)['\"]\\);~", $code, $matches);
        if ($res) {
            $this->scenario->setFeature("test ".$matches[1]);
            return;
        }
    }

    public function parseScenarioOptions($code)
    {
        $matches = array();
        $res = preg_match_all("~\\\$scenario->.*?;~", $code, $matches);
        if (!$res) {
            return;
        }
        $scenario = $this->scenario;
        foreach ($matches[0] as $line) {
            eval($line);
        }
    }

    public function parseSteps($code)
    {
        $res = preg_match_all("~\\\$I->(.*)\((.*?)\);~", $code, $matches);
        if (!$res) {
            return;
        }

        foreach ($matches[0] as $k => $all) {
            $action = $matches[1][$k];
            $params = $matches[2][$k];
            if (in_array($action, array('wantTo','wantToTest'))) {
                continue;
            }
            $this->scenario->addStep(new Step\Action($action, explode(',', $params)));
        }

    }

}
