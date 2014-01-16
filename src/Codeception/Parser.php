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

}
