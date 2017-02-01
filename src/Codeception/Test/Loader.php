<?php
namespace Codeception\Test;

use Codeception\Test\Loader\Cept as CeptLoader;
use Codeception\Test\Loader\Cest as CestLoader;
use Codeception\Test\Loader\Unit as UnitLoader;
use Codeception\Test\Loader\Gherkin as GherkinLoader;
use Symfony\Component\Finder\Finder;

/**
 * Loads all Codeception supported test formats from a directory.
 *
 * ``` php
 * <?php
 * $testLoader = new \Codeception\TestLoader('tests/unit');
 * $testLoader->loadTests();
 * $tests = $testLoader->getTests();
 * ?>
 * ```
 * You can load specific file
 *
 * ``` php
 * <?php
 * $testLoader = new \Codeception\TestLoader('tests/unit');
 * $testLoader->loadTest('UserTest.php');
 * $testLoader->loadTest('PostTest.php');
 * $tests = $testLoader->getTests();
 * ?>
 * ```
 * or a subdirectory
 *
 * ``` php
 * <?php
 * $testLoader = new \Codeception\TestLoader('tests/unit');
 * $testLoader->loadTest('models'); // all tests from tests/unit/models
 * $tests = $testLoader->getTests();
 * ?>
 * ```
 *
 */
class Loader
{
    protected $formats = [];
    protected $tests = [];
    protected $path;

    public function __construct(array $suiteSettings)
    {
        $this->path = $suiteSettings['path'];
        $this->formats = [
            new CeptLoader(),
            new CestLoader(),
            new UnitLoader(),
            new GherkinLoader($suiteSettings)
        ];
    }

    public function getTests()
    {
        return $this->tests;
    }

    protected function relativeName($file)
    {
        return str_replace([$this->path, '\\'], ['', '/'], $file);
    }

    protected function findPath($path)
    {
        if (!file_exists($path)
            && substr($path, -strlen('.php')) !== '.php'
            && file_exists($newPath = $path . '.php')
        ) {
            return $newPath;
        }

        return $path;
    }

    protected function makePath($originalPath)
    {
        $path = $this->path . $this->relativeName($originalPath);

        if (file_exists($newPath = $this->findPath($path))
            || file_exists($newPath = $this->findPath(getcwd() . "/{$originalPath}"))
        ) {
            $path = $newPath;
        }

        if (!file_exists($path)) {
            throw new \Exception("File or path $originalPath not found");
        }

        return $path;
    }

    public function loadTest($path)
    {
        $path = $this->makePath($path);

        foreach ($this->formats as $format) {
            /** @var $format Loader  **/
            if (preg_match($format->getPattern(), $path)) {
                $format->loadTests($path);
                $this->tests = $format->getTests();
                return;
            }
        }

        if (is_dir($path)) {
            $currentPath = $this->path;
            $this->path = $path;
            $this->loadTests();
            $this->path = $currentPath;
            return;
        }
        throw new \Exception('Test format not supported. Please, check you use the right suffix. Available filetypes: Cept, Cest, Test');
    }

    public function loadTests($fileName = null)
    {
        if ($fileName) {
            return $this->loadTest($fileName);
        }

        $finder = Finder::create()->files()->sortByName()->in($this->path)->followLinks();

        foreach ($this->formats as $format) {
            /** @var $format Loader  **/
            $formatFinder = clone($finder);
            $testFiles = $formatFinder->name($format->getPattern());
            foreach ($testFiles as $test) {
                $pathname = str_replace(["//", "\\\\"], ["/", "\\"], $test->getPathname());
                $format->loadTests($pathname);
            }
            $this->tests = array_merge($this->tests, $format->getTests());
        }
    }
}
