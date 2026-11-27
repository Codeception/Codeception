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
use PhpToken;

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
        $tokens = PhpToken::tokenize(file_get_contents($file), TOKEN_PARSE);
        $classes = [];
        $namespace = '';

        foreach ($tokens as $i => $token) {
            if ($token->is(T_NAMESPACE)) {
                $name = self::nextSignificantToken($tokens, $i);
                $namespace = $name?->is([T_STRING, T_NAME_QUALIFIED]) ? $name->text . '\\' : '';
            }
            // Only a class declaration has a name right after the keyword, unlike
            // anonymous classes (`new readonly class (...)`), `Foo::class` and named arguments (`class: ...`)
            if ($token->is(T_CLASS)) {
                $name = self::nextSignificantToken($tokens, $i);
                if ($name?->is(T_STRING)) {
                    $classes[] = $namespace . $name->text;
                }
            }
        }

        gc_mem_caches();
        return $classes;
    }

    /**
     * @param PhpToken[] $tokens
     */
    private static function nextSignificantToken(array $tokens, int $index): ?PhpToken
    {
        $counter = count($tokens);
        for ($j = $index + 1; $j < $counter; ++$j) {
            if (!$tokens[$j]->isIgnorable()) {
                return $tokens[$j];
            }
        }
        return null;
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
