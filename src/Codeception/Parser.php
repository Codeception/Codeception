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
        // parse per line
        $friends = [];
        $lines = explode("\n", $code);
        $isFriend = false;
        foreach ($lines as $line) {
            // friends
            if (preg_match("~\\\$I->haveFriend\((.*?)\);~", $line, $matches)) {
                $friends[] = trim($matches[1],'\'"');
            }
            // friend's section start
            if (preg_match("~\\\$(.*?)->does\(~", $line, $matches)) {
                if (!in_array($friend = $matches[1], $friends)) {
                    continue;
                }
                $isFriend = true;
                $this->addCommentStep("\n----- $friend does -----");
                continue;
            }

            // actions
            if (preg_match("~\\\$I->(.*)\((.*?)\);~", $line, $matches)) {
                $this->addStep($matches);
            }

            // friend's section ends
            if ($isFriend and strpos($line, '}') !== false) {
                $this->addCommentStep("-------- back to me\n");
                $isFriend = false;
            }
        }

    }

    protected function addStep($matches)
    {
        list($m, $action, $params) = $matches;
        if (in_array($action, array('wantTo','wantToTest'))) {
            return;
        }
        $this->scenario->addStep(new Step\Action($action, explode(',', $params)));
    }

    protected function addCommentStep($comment)
    {
        $this->scenario->addStep(new \Codeception\Step\Comment($comment,array()));
    }

}
