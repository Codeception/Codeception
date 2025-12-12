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
    protected string $code;

    public function __construct(protected Scenario $scenario, protected Metadata $metadata)
    {
    }

    public function prepareToRun(string $code): void
    {
        $this->parseFeature($code);
        $this->parseScenarioOptions($code);
    }

    public function parseFeature(string $code): void
    {
        $code = $this->stripComments($code);
        if (preg_match("#\\\$I->(wantTo|wantToTest)\\(\\s*?['\"](.*?)['\"]\\s*?\\);#", $code, $matches)) {
            $feature = $matches[1] === 'wantToTest' ? "test {$matches[2]}" : $matches[2];
            $this->scenario->setFeature($feature);
        }
    }

    public function parseScenarioOptions(string $code): void
    {
        $this->metadata->setParamsFromAnnotations($this->matchComments($code));
    }

    public function parseSteps(string $code): void
    {
        $friends = [];
        $lines = explode("\n", $code);
        $isFriend = false;

        foreach ($lines as $line) {
            if (preg_match("#\\\$I->haveFriend\\((.*?)\\);#", $line, $matches)) { // Friends
                $friends[] = trim($matches[1], '\'"');
            }
            if (preg_match("#\\\$(.*?)->does\\(#", $line, $matches)) { // Friends section start
                $friend = $matches[1];
                if (!in_array($friend, $friends)) {
                    continue;
                }
                $isFriend = true;
                $this->addCommentStep("\n----- {$friend} does -----");
                continue;
            }
            if (preg_match("#\\\$I->(.*)\\((.*?)\\);#", $line, $matches)) { // Actions
                $this->addStep($matches);
            }
            if ($isFriend && str_contains($line, '}')) { // Friends section ends
                $this->addCommentStep("-------- back to me\n");
                $isFriend = false;
            }
        }
    }

    /** @param string[] $matches */
    protected function addStep(array $matches): void
    {
        [$m, $action, $params] = $matches;
        if (!in_array($action, ['wantTo', 'wantToTest'])) {
            $this->scenario->addStep(new Action($action, explode(',', $params)));
        }
    }

    protected function addCommentStep(string $comment): void
    {
        $this->scenario->addStep(new Comment($comment, []));
    }

    public static function load(string $file): void
    {
        try {
            self::includeFile($file);
        } catch (ParseError $e) {
            throw new TestParseException($file, $e->getMessage(), $e->getLine());
        } catch (Exception) {
            // file is valid otherwise
        }
    }

    /**
     * @return string[]
     */
    public static function getClassesFromFile(string $file): array
    {
        $sourceCodeTokens = token_get_all(file_get_contents($file), TOKEN_PARSE);
        $classes = [];
        $namespace = '';

        foreach ($sourceCodeTokens as $i => $token) {
            if ($token[0] === T_NAMESPACE) {
                $namespace = self::extractNamespace($sourceCodeTokens, $i);
            }
            if ($token[0] === T_CLASS) {
                $class = self::extractClass($sourceCodeTokens, $i);
                if ($class) {
                    $classes[] = $namespace . $class;
                }
            }
        }

        gc_mem_caches();
        return $classes;
    }

    private static function extractNamespace(array $tokens, int $index): string
    {
        $namespace = '';
        $counter = count($tokens);
        for ($j = $index + 1; $j < $counter; ++$j) {
            if ($tokens[$j] === '{' || $tokens[$j] === ';') {
                break;
            }
            if ($tokens[$j][0] === T_STRING || $tokens[$j][0] === T_NAME_QUALIFIED) {
                $namespace .= $tokens[$j][1] . '\\';
            }
        }
        return $namespace;
    }

    private static function extractClass(array $tokens, int $index): ?string
    {
        // class at the beginning of file
        if (!isset($tokens[$index - 2])) {
            return $tokens[$index + 2][1] ?? null;
        }
        // new class
        if (isset($tokens[$index - 2]) && $tokens[$index - 2][0] === T_NEW) {
            return null;
        }
        // :: class
        if (isset($tokens[$index - 1]) && $tokens[$index - 1][0] === T_WHITESPACE && isset($tokens[$index - 2]) && $tokens[$index - 2][0] === T_DOUBLE_COLON) {
            return null;
        }
        // ::class
        if (isset($tokens[$index - 1]) && $tokens[$index - 1][0] === T_DOUBLE_COLON) {
            return null;
        }
        // class{
        if (isset($tokens[$index + 1]) && $tokens[$index + 1] === '{') {
            return null;
        }
        // class {
        if (isset($tokens[$index + 2]) && $tokens[$index + 1][0] === T_WHITESPACE && $tokens[$index + 2] === '{') {
            return null;
        }
        return $tokens[$index + 2][1] ?? null;
    }

    /*
     * Include in different scope to prevent included file from affecting $file variable
     */
    private static function includeFile(string $file): void
    {
        include_once $file;
    }

    protected function stripComments(string $code): string
    {
        return preg_replace(['#//.*?$#m', '#/*\*.*?\*/#ms'], '', $code); // inline & block comments
    }

    protected function matchComments(string $code): string
    {
        preg_match_all('#//(.*?)$#m', $code, $lineMatches);
        preg_match('#/\*(.*?)\*/#ms', $code, $blockMatch);
        $lineComments = implode("\n", $lineMatches[1] ?? []);
        $blockComments = $blockMatch[1] ?? '';

        return $lineComments . "\n" . $blockComments . "\n";
    }
}
