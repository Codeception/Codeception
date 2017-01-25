<?php
namespace Codeception\Lib;

use Codeception\Configuration;
use Codeception\Exception\TestParseException;
use Codeception\Scenario;
use Codeception\Step;
use Codeception\Test\Metadata;
use Codeception\Util\Annotation;

class Parser
{
    /**
     * @var Scenario
     */
    protected $scenario;
    /**
     * @var Metadata
     */
    protected $metadata;
    protected $code;

    public function __construct(Scenario $scenario, Metadata $metadata)
    {
        $this->scenario = $scenario;
        $this->metadata = $metadata;
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
        $comments = $this->matchComments($code);
        $this->attachMetadata($comments);
    }

    public function attachMetadata($comments)
    {
        $this->metadata->setGroups(Annotation::fetchAllFromComment('group', $comments));
        $this->metadata->setEnv(Annotation::fetchAllFromComment('env', $comments));
        $this->metadata->setDependencies(Annotation::fetchAllFromComment('depends', $comments));
        $this->metadata->setSkip($this->firstOrNull(Annotation::fetchAllFromComment('skip', $comments)));
        $this->metadata->setIncomplete($this->firstOrNull(Annotation::fetchAllFromComment('incomplete', $comments)));
    }

    private function firstOrNull($array)
    {
        if (empty($array)) {
            return null;
        }
        return (string)$array[0];
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

    public static function validate($file)
    {
        $config = Configuration::config();
        if (empty($config['settings']['lint'])) { // lint disabled in config
            return;
        }
        if (!function_exists('exec')) {
            //exec function is disabled #3324
            return;
        }
        exec("php -l " . escapeshellarg($file) . " 2>&1", $output, $code);
        if ($code !== 0) {
            throw new TestParseException($file, implode("\n", $output));
        }
    }

    public static function load($file)
    {
        if (PHP_MAJOR_VERSION < 7) {
            self::validate($file);
        }
        try {
            self::includeFile($file);
        } catch (\ParseError $e) {
            throw new TestParseException($file, $e->getMessage());
        } catch (\Exception $e) {
            // file is valid otherwise
        }
    }

    public static function getClassesFromFile($file)
    {
        $sourceCode = file_get_contents($file);
        $classes = [];
        $tokens = token_get_all($sourceCode);
        $tokenCount = count($tokens);
        $namespace = '';

        for ($i = 0; $i < $tokenCount; $i++) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = '';
                for ($j = $i + 1; $j < $tokenCount; $j++) {
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
                if ($tokens[$i - 2][0] === T_NEW) {
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
