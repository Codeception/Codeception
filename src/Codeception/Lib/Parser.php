<?php
namespace Codeception\Lib;

use Codeception\Scenario;
use Codeception\Step;
use Codeception\Util\Annotation;

class Parser
{
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
        $matches = [];
        $code = $this->stripComments($code);
        $res = preg_match("~\\\$I->wantTo\\(\s*?['\"](.*?)['\"]\s*?\\);~", $code, $matches);
        if ($res) {
            $this->scenario->setFeature($matches[1]);
            return;
        }
        $res = preg_match("~\\\$I->wantToTest\\(['\"](.*?)['\"]\\);~", $code, $matches);
        if ($res) {
            $this->scenario->setFeature("test " . $matches[1]);
            return;
        }
    }

    public function parseScenarioOptions($code)
    {
        $annotations = ['group', 'env', 'skip', 'incomplete', 'ignore'];
        $comments = $this->matchComments($code);
        foreach ($annotations as $annotation) {
            $values = Annotation::fetchAllFromComment($annotation, $comments);
            foreach ($values as $value) {
                call_user_func([$this->scenario, $annotation], $value);
            }
        }

        // deprecated - parsing $scenario->xxx calls
        $metaData = ['group', 'env'];
        $phpCode = $this->stripComments($code);
        $scenario = $this->scenario;
        $feature = $scenario->getFeature();
        foreach ($metaData as $call) {
            $res = preg_match_all("~\\\$scenario->$call.*?;~", $phpCode, $matches);
            if (!$res) {
                continue;
            }
            foreach ($matches[0] as $line) {
                // run $scenario->group or $scenario->env
                \Codeception\Lib\Deprecation::add("\$scenario->$call() is deprecated in favor of annotation: // @$call",
                    $this->scenario->getFeature()
                );
                eval($line);
            }

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
                $friends[] = trim($matches[1], '\'"');
            }
            // friend's section start
            if (preg_match("~\\\$(.*?)->does\(~", $line, $matches)) {
                $friend = $matches[1];
                if (!in_array($friend, $friends)) {
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
            if ($isFriend && strpos($line, '}') !== false) {
                $this->addCommentStep("-------- back to me\n");
                $isFriend = false;
            }
        }

    }

    protected function addStep($matches)
    {
        list($m, $action, $params) = $matches;
        if (in_array($action, ['wantTo', 'wantToTest'])) {
            return;
        }
        $this->scenario->addStep(new Step\Action($action, explode(',', $params)));
    }

    protected function addCommentStep($comment)
    {
        $this->scenario->addStep(new \Codeception\Step\Comment($comment, []));
    }

    public static function getClassesFromFile($file)
    {
        self::includeFile($file);
        $sourceCode = file_get_contents($file);
        $classes = [];
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
                if (!isset($tokens[$i - 2])) {
                    $classes[] = $namespace . $tokens[$i + 2][1];
                    continue;
                }
                if ($tokens[$i - 1][0] === T_WHITESPACE and $tokens[$i - 2][0] === T_DOUBLE_COLON) {
                    continue;
                }
                if ($tokens[$i - 1][0] === T_DOUBLE_COLON) {
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

    protected function matchComments($code)
    {
        $matches = [];
        $comments = '';
        $hasLineComment = preg_match_all('~\/\/(.*?)$~m', $code, $matches);
        if ($hasLineComment) {
            foreach ($matches[1] as $line) {
                $comments .= $line."\n";
            }
        }
        $hasBlockComment = preg_match('~\/*\*(.*?)\*\/~ms', $code, $matches);
        if ($hasBlockComment) {
            $comments .= $matches[1]."\n";
        }
        return $comments;
    }
}
