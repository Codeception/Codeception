<?php

declare(strict_types=1);

namespace Codeception\Lib;

use Codeception\Exception\TestParseException;
use Codeception\Scenario;
use Codeception\Step\Action;
use Codeception\Step\Comment;
use Codeception\Test\Metadata;
use Exception;
use ParseError;

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

    public function prepareToRun($code): void
    {
        $this->parseFeature($code);
        $this->parseScenarioOptions($code);
    }

    public function parseFeature($code): void
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

    public function parseScenarioOptions($code): void
    {
        $this->metadata->setParamsFromAnnotations($this->matchComments($code));
    }

    public function parseSteps($code): void
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
                $this->addCommentStep("\n----- {$friend} does -----");
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

    protected function addStep($matches): void
    {
        list($m, $action, $params) = $matches;
        if (in_array($action, ['wantTo', 'wantToTest'])) {
            return;
        }
        $this->scenario->addStep(new Action($action, explode(',', $params)));
    }

    protected function addCommentStep($comment): void
    {
        $this->scenario->addStep(new Comment($comment, []));
    }

    public static function load($file): void
    {
        try {
            self::includeFile($file);
        } catch (ParseError $e) {
            throw new TestParseException($file, $e->getMessage(), (string)$e->getLine());
        } catch (Exception $e) {
            // file is valid otherwise
        }
    }

    /**
     * @return string[]
     */
    public static function getClassesFromFile($file): array
    {
        $sourceCode = file_get_contents($file);
        $classes = [];
        $tokens = token_get_all($sourceCode);
        $tokenCount = count($tokens);
        $namespace = '';

        for ($i = 0; $i < $tokenCount; ++$i) {
            if ($tokens[$i][0] === T_NAMESPACE) {
                $namespace = '';
                for ($j = $i + 1; $j < $tokenCount; ++$j) {
                    if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                        break;
                    }
                    if ($tokens[$j][0] === T_STRING || (PHP_MAJOR_VERSION >= 8 && $tokens[$j][0] === T_NAME_QUALIFIED)) {
                        $namespace .= $tokens[$j][1] . '\\';
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
                if ($tokens[$i - 1][0] === T_WHITESPACE && $tokens[$i - 2][0] === T_DOUBLE_COLON) {
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
    private static function includeFile($file): void
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

    protected function matchComments($code): string
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
