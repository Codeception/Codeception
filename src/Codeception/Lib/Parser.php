<?php
namespace Codeception\Lib;

use Codeception\Scenario;
use Codeception\Step;

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
        $code = $this->stripComments($code);
        $res = preg_match("~\\\$I->wantTo\\(\s*?['\"](.*?)['\"]\s*?\\);~", $code, $matches);
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

    public function parseScenarioOptions($code, $var = 'scenario')
    {
        $matches = array();
        $code = $this->stripComments($code);
        $res = preg_match_all("~\\\$$var->.*?;~", $code, $matches);
        if (!$res or !$var) {
            return;
        }
        $$var = $this->scenario;
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

    public static function getClassesFromFile($file)
    {
        self::includeFile($file);
        $sourceCode = file_get_contents($file);
        $classes = array();
        $tokens = token_get_all($sourceCode);
        $namespace = '';

        for ($i = 0; $i < count($tokens); $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = '';
                for ($j = $i + 1; $j < count($tokens); $j++) {
                    if ($tokens[$j][0] === T_STRING) {
                        $namespace .= $tokens[$j][1] . '\\';
                    } else {
                        if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                            break;
                        }
                    }
                }
            }

            if ($tokens[$i][0] === T_CLASS) {
                if (!isset($tokens[$i-2])) {
                    $classes[] = $namespace . $tokens[$i + 2][1];
                    continue;
                }
                if ($tokens[$i-1][0] === T_WHITESPACE and $tokens[$i-2][0] === T_DOUBLE_COLON) {
                    continue;
                }
                if ($tokens[$i-1][0] === T_DOUBLE_COLON) {
                    continue;
                }
                $classes[] = $namespace . $tokens[$i + 2][1];
            }
        }

        return $classes;
    }
    
    /*
     * Include in different scope to prevent included file from affecting $file variable
     */ 
    private static function includeFile($file)
    {
        include_once $file;
    }

    /**
     * @param $code
     * @return mixed
     */
    protected function stripComments($code)
    {
        $code = preg_replace('~\/\/.*?$~m', '', $code); // remove inline comments
        $code = preg_replace('~\/*\*.*?\*\/~ms', '', $code);
        return $code; // remove block comment
    }

}
