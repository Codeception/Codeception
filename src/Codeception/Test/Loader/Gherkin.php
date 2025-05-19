<?php

declare(strict_types=1);

namespace Codeception\Test\Loader;

use Behat\Gherkin\Filter\RoleFilter;
use Behat\Gherkin\Keywords\CachedArrayKeywords as GherkinKeywords;
use Behat\Gherkin\Lexer as GherkinLexer;
use Behat\Gherkin\Node\ExampleNode;
use Behat\Gherkin\Node\FeatureNode;
use Behat\Gherkin\Node\OutlineNode;
use Behat\Gherkin\Node\ScenarioNode;
use Behat\Gherkin\Parser as GherkinParser;
use Codeception\Configuration;
use Codeception\Exception\ParseException;
use Codeception\Exception\TestParseException;
use Codeception\Lib\Generator\Shared\Classname;
use Codeception\Test\Gherkin as GherkinFormat;
use Codeception\Util\Annotation;

use function array_keys;
use function array_map;
use function array_merge;
use function class_exists;
use function file_get_contents;
use function get_class_methods;
use function glob;
use function implode;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function sprintf;
use function str_replace;

class Gherkin implements LoaderInterface
{
    use Classname;

    protected static array $defaultSettings = [
        'namespace' => '',
        'actor' => '',
        'gherkin' => [
            'contexts' => [
                'default' => [],
                'tag' => [],
                'role' => []
            ]
        ]
    ];

    /**
     * @var GherkinFormat[]
     */
    protected array $tests = [];

    protected GherkinParser $parser;

    protected array $settings = [];

    protected array $steps = [];

    /**
     * @param array<string, mixed> $settings
     * @throws TestParseException
     */
    public function __construct(array $settings = [])
    {
        $this->settings = Configuration::mergeConfigs(self::$defaultSettings, $settings);
        if (!class_exists(GherkinKeywords::class)) {
            throw new TestParseException('Feature file can only be parsed with Behat\Gherkin library. Please install `behat/gherkin` with Composer');
        }
        $keywords = GherkinKeywords::withDefaultKeywords();
        $lexer = new GherkinLexer($keywords);
        $this->parser = new GherkinParser($lexer);
        $this->fetchGherkinSteps();
    }

    protected function fetchGherkinSteps(): void
    {
        $contexts = $this->settings['gherkin']['contexts'];

        foreach ($contexts['tag'] as $tag => $tagContexts) {
            $this->addSteps($tagContexts, "tag:{$tag}");
        }
        foreach ($contexts['role'] as $role => $roleContexts) {
            $this->addSteps($roleContexts, "role:{$role}");
        }

        if ($this->steps === [] && empty($contexts['default']) && $this->settings['actor']) { // if no context is set, actor to be a context
            $actorContext = $this->supportNamespace() . $this->settings['actor'];
            if ($actorContext) {
                $contexts['default'][] = $actorContext;
            }
        }

        if (
            isset($this->settings['gherkin']['contexts']['path']) &&
            isset($this->settings['gherkin']['contexts']['namespace_prefix'])
        ) {
            $files = glob($this->settings['gherkin']['contexts']['path'] . '/*/*.php');

            // Strip off include path
            $files = str_replace([$this->settings['gherkin']['contexts']['path'], '.php', '/'], ['', '', '\\'], $files);

            // Add namespace prefix
            $namespace = $this->settings['gherkin']['contexts']['namespace_prefix'];
            $dynamicContexts = array_map(fn ($path): string => $namespace . $path, $files);

            $this->addSteps($dynamicContexts);
        }

        $this->addSteps($contexts['default']);
    }

    protected function addSteps(array $contexts, string $group = 'default'): void
    {
        if (!isset($this->steps[$group])) {
            $this->steps[$group] = [];
        }

        foreach ($contexts as $context) {
            if (is_string($context) && !class_exists($context)) {
                throw new \InvalidArgumentException(
                    sprintf("Context class %s does not exist", $context)
                );
            }
            $methods = get_class_methods((new \ReflectionClass($context))->newInstanceWithoutConstructor());

            if ($methods === []) {
                continue;
            }
            foreach ($methods as $method) {
                $annotation = Annotation::forMethod($context, $method);
                foreach (['Given', 'When', 'Then'] as $type) {
                    $patterns = $annotation->fetchAll($type);
                    foreach ($patterns as $pattern) {
                        if (!$pattern) {
                            continue;
                        }
                        $this->validatePattern($pattern);
                        $pattern = $this->makePlaceholderPattern($pattern);
                        $this->steps[$group][$pattern] = [$context, $method];
                    }
                }
            }
        }
    }

    public function makePlaceholderPattern(string $pattern): string
    {
        if (isset($this->settings['describe_steps'])) {
            return $pattern;
        }
        if (!str_starts_with($pattern, '/')) {
            $pattern = preg_quote($pattern);

            $pattern = preg_replace('#(\w+)/(\w+)#', '(?:$1|$2)', $pattern); // or
            $pattern = preg_replace('#\\\\\((\w)\\\\\)#', '$1?', $pattern); // (s)

            $replacePattern = sprintf(
                '(?|\"%s\"|%s)',
                "((?|[^\"\\\\\\]|\\\\\\.)*?)", // matching escaped string in ""
                '[\D]{0,1}([\d\,\.]+)[\D]{0,1}'
            ); // or matching numbers with optional $ or € chars

            // params converting from :param to match 11 and "aaa" and "aaa\"aaa"
            $pattern = preg_replace('#"?\\\:(\w+)"?#', $replacePattern, $pattern);
            $pattern = "#^{$pattern}$#u";
            // validating this pattern is slow, so we skip it now
        }
        return $pattern;
    }

    private function validatePattern(string $pattern): void
    {
        if (!str_starts_with($pattern, '/')) {
            return; // not a user-regex but a string with placeholder
        }
        if (@preg_match($pattern, ' ') === false) {
            throw new ParseException("Loading Gherkin step with regex\n \n{$pattern}\n \nfailed. This regular expression is invalid.");
        }
    }

    public function loadTests(string $filename): void
    {
        $featureNode = $this->parser->parse(file_get_contents($filename), $filename);

        if (!$featureNode instanceof FeatureNode) {
            return;
        }

        foreach ($featureNode->getScenarios() as $scenarioNode) {
            $steps = $this->steps['default']; // load default context

            foreach (array_merge($scenarioNode->getTags(), $featureNode->getTags()) as $tag) { // load tag contexts
                if (isset($this->steps["tag:{$tag}"])) {
                    $steps = array_merge($steps, $this->steps["tag:{$tag}"]);
                }
            }

            $roles = $this->settings['gherkin']['contexts']['role']; // load role contexts
            foreach (array_keys($roles) as $role) {
                $filter = new RoleFilter($role);
                if ($filter->isFeatureMatch($featureNode)) {
                    $steps = array_merge($steps, $this->steps["role:{$role}"]);
                    break;
                }
            }

            if ($scenarioNode instanceof OutlineNode) {
                foreach ($scenarioNode->getExamples() as $example) {
                    /** @var ExampleNode $example */
                    $params = implode(', ', $example->getTokens());
                    $exampleNode = new ScenarioNode(
                        $scenarioNode->getTitle() . " | {$params}",
                        $scenarioNode->getTags(),
                        $example->getSteps(),
                        $example->getKeyword(),
                        $example->getLine()
                    );
                    $this->tests[] = new GherkinFormat($featureNode, $exampleNode, $steps);
                }
                continue;
            }
            $this->tests[] = new GherkinFormat($featureNode, $scenarioNode, $steps);
        }
    }

    /**
     * @return GherkinFormat[]
     */
    public function getTests(): array
    {
        return $this->tests;
    }

    public function getPattern(): string
    {
        return '~\.feature$~';
    }

    public function getSteps(): array
    {
        return $this->steps;
    }
}
